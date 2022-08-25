# Phalcon Framework Auth Plugin

Kullanıcı giriş bilgilerini oturumda saklar ve kullanıcının giriş yapıp yapmadığını oturun bilgisi ile kontrol eder.

Bir kullanıcının sadece bir ip den bağlı kalmasını sağlar.

## Kurulum

- Bağımlılık kapına auth servisi kaydedilir.

```php
$di->register(new AuthProvider());
```

- Kullanıcı izinlerini almak için 'AuthDataServiceInterface' arayüzünü uygulayan bir sınıf oluşturup bunu 'authDataService' isminde bağımlılık kapına kaydedilir.

### Giriş Örneği

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

## İzin Yönetimi

İzin kontrolü izin kodu ve düzey değerlerine göre yapılır.

İzin Kodu: Ugulamada izin verilecek her sayfa yada işlem için bir kod belirlenir.

İzin Düzeyi Seçenekleri:

- 3: Okuma
- 5: Okuma, Yazma
- 7: Okuma, Yazma, Güncelleme
- 9: Okuma, Yazma, Güncelleme, Silme

Örneğin: Kullanıcı işlemleri için 100 kodu ve yetkisi düzeyi en az 3(Okuma) verilir. Kullanıcı listesini oluşturmadan önce aşağıdaki kontrolü yaparak
kullanıcının sayfaya yetkisi varmı kontrol edilir.

```php
    $result = $auth->hasPermission(100, 3);

    if ($result)
        echo 'Yetkisi var';
    else
        echo 'Yetkisi yok';
```

### Annotation İle Yetki Kontrolü

Uygulamada bir kaynağın izin kodu ve düzeyi annotation ile tanımlanabilir.

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

Uygulama genelinde bu kontrolü yapmak için Dispatcher servisinin olay dinleyicisine aşağıdakı örnek yapı kulanılabilir.

```php
class DispatchEventHandler extends Injectable
{
    public function beforeDispatchLoop(Event $event, Dispatcher $dispatcher): bool
    {
        if (!$this->auth->isLogin())
        {
            $this->response->redirect(['for' => 'user-login']);
            return false;
        }
            
        $hasPermission = $this->auth->hasAllowedResource(
            $dispatcher->getControllerClass(),
            $dispatcher->getActiveMethod(),
            $dispatcher->getModuleName()
        );
        if (!$hasPermission)
            return false;

        return true;
    }
}
```

### AuthDataServiceInterface Uygulama Örneği

Modüler yapı kullanıyorsanız izin değerleri modül isimlerine göre gruplanıp gönderilir. Modüller arasında izin kodları
aynı olabilir.

```php
class AuthDataService implements AuthDataServiceInterface
{
    public function getPermissions($userId)
    {
        return [
            'user' => [
                '100' => 9,
                '101' => 9
            ],
            'customer' => [
                '100' => 9,
                '101' => 3
            ]
        ];
    }
}
```

Modül kullanılmıyorsa

```php
class AuthDataService implements AuthDataServiceInterface
{
    public function getPermissions($userId)
    {
        return [
            '100' => 5,
            '101' => 9
        ];
    }
}
```
