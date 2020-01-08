# service-container

### PHP service container


#### Installation

```
$ composer require alireza-h/service-container
```



Register service

```
ServiceContainer::getInstance()
            ->register(AuthService::class)
            ->register(RouteCollection::class)
            ->register(RequestContext::class)
            ->register(UrlService::class, null, [RouteCollection::class, RequestContext::class]);
```

Access to registered service
```
ServiceContainer::getInstance()
            ->service(AuthService::class);
            
ServiceContainer::getInstance()
            ->service(UrlService::class);
```
