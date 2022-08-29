# Phalcon Framework Auth Plugin

Outurum bilgisi ile bir kullanıcının giriş yapıp yapmadığını kontrol eder.

- Aynı anda sadece bir ip adresinden girişe izin verilir.
- Kullanıcıların sayfalara erişim izni olup olmadığını kontrol edebilirsiniz.
- Ayarlardan aktif edilirse sadece izin verilen ip adreslerinden erişim yapılmasını sağlayabilirsiniz.

Not: Yetki ve ip adresi izni için 'AuthDataServiceInterface' sınıfını uygulayan bir sınıfınız olmalıdır. Kullanıcıların
yetkileri ve izin verilen ip adresleri bu sınıf aracılığı ile alınır.

## Kurulum

```
composer require secgin/phalcon-auth-plugin
```

## Uygulama Adımları

### Servis kaydedilir

```php
$di->register(new AuthProvider());
```

### İzinler ve Ip Adresileri (AuthDataServiceInterface)

Kullanıcı izinlerini almak için 'AuthDataServiceInterface' arayüzünü uygulayan bir sınıf oluşturup 'authDataService'
isminde kaydedilir.

```php
class AuthDataService implements AuthDataServiceInterface
{
    private array $permissions = [
        '100' => 7,
        '101' => 9,
        'user' => [
            '100' => 9,
            '101' => 9
        ],
        'customer' => [
            '100' => 9,
            '101' => 3
        ]
    ];

    private array $allowedIpAddresses = [
        '127.0.0.1',
        '::1',
        '192.168.1.42'
    ];

    public function getPermissionLevel(string $permissionCode, ?string $moduleName = null): ?int
    {
        return $moduleName != ''
            ? $this->permissions[$moduleName][$permissionCode] ?? null
            : $this->permissions[$permissionCode] ?? null;
    }

    public function isIpAddressAllowed(string $ipAddress): bool
    {
        return in_array($ipAddress, $this->allowedIpAddresses);
    }
}
```

```php
$container->setShared('authDataService', AuthDataService::class);
```

### <a name="authDataServiceInterface"></a>Sayfaların yetkilerinin kontrol edildiği adım

Dispatcher servisinin olay dinleyicisine aşığadaki sınıfı uygulayıp açılan sayfaların yetki kontrolü yapılır.

```php
class DispatcherEventHandler extends Injectable
{
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher): bool
    {
        $result = $this->auth->hasAllowed(
            $dispatcher->getControllerClass(),
            $dispatcher->getActiveMethod(),
            $dispatcher->getModuleName());

        switch ((string)$result)
        {
            case AuthInterface::NOT_LOGGED_IN:
                $this->response
                    ->redirect('user');
                return false;
            case AuthInterface::NOT_ALLOWED_IP_ADDRESS:
                $this->response
                    ->redirect('user/ip');
                return false;
            case AuthInterface::NOT_ALLOWED_RESOURCE:
                $dispatcher->forward([
                    'controller' => 'error',
                    'action' => 'show401'
                ]);
                return false;
        }

        return true;
    }
}
```

### Giriş İşlemi

Kullanıcının adı ve şifresini doğruladıktan sonra login işlemini başlatmalısınız.

```php
try
{
    $auth->login([
        'id' => '1',
        'usernamea' => 'admin',
        'name' => 'Admin',
    ]);
}
catch (LoginRequiredFieldException $e)
{
    echo $e->getMessage();
}
catch (Exception $e)
{
    echo 'Giriş yapılmadı.';
}
```

### Ek Açıklamalar(Annotation) Yetkilerin Belirlenmesi

İzin kontrolü izin kodu ve düzey değerlerine göre yapılır.

İzin Kodu: Ugulamada izin verilecek her işlem için bir kod belirlenir. Bu kodları 'AuthDataServiceInterface' arayüzünü
uygulayan sınıf ile alınır.

İzin Düzeyi Seçenekleri:

- 3: Okuma
- 5: Okuma, Yazma
- 7: Okuma, Yazma, Güncelleme
- 9: Okuma, Yazma, Güncelleme, Silme

Ek açıklamalar

- @Public: Sayfanın yada işlemin herkese açık olduğunu belirtir. Örneğin login sayfası.
- @Private: Erişim kısıtlaması olan sayfalar için kullanılır. Bu açıklama varsayılan değerdir, eğer izin kodu ve düzeyi
  belirtilmesse sadece kullanıcını giriş yapması sayafaya erişim için yeterli olur.
- @IpAllowed: Bu açıklama ise bir kullanıcının tüm ip adreslerinden giriş yapmasını sağlar. Örneğin kullanıcıya ip izni
  vermek için kulanılan sayfaya bu açıklama eklenir.

### Private kullanımı

Sınıf için @Private(izin kodu)

Method için @Private(izin kodu, izin düzeyi(zorunlu değil))

```php
/**
* @Private(100)
 */
class UserController extends Controller 
{
    /**
    * @Private(3, 100) 
     */
    public function listAction()
    {
    
    }
    
    /**
    * @Private(5) 
     */
    public function newAction()
    {
    
    }
    
    /**
    * @Private(7) 
     */
    public function editAction()
    {
    
    }
    
    /**
    * @Private(9) 
     */
    public function deleteAction()
    {
    
    }
}
```

@Private(3, 100) Burada ilk parametre listAction için yetki düzeyinin en az 3 olması gerektiği, yetki kodunun ise 100
olduğunu verir.

Yetki kodu yazılmaz ise sınıfa tanımlı yetki kodu baz alınır.

## Ayarlar

Projenin config dosyasında auth isminde bir ayar grubu oluşturulur. Varsayılan değerler ile işlem yapıalcak ise
aşağıdaki seçenekler girilmeyebilir.

```php
new Config([
    'application' => [
        'cacheDir' => BASE_PATH . '/var/cache/',
    ],
    'auth' => [
        'useAllowedIpAddress' => f,
        'defaultAction' => 0
    ]
]);
```

- useAllowedIpAddress(bool[false]): Sadece izin verilen ip adreslerine erişim izni vermek için 'true' gönderilir.
- defaultAction(int): 0-1 değerlerini alır. 0 varsayılan olarak tüm sayfalar private, 1 ise public olarak işlem yapılır.