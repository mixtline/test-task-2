Catch event kernel.request 
=====

0. Create a new symfony3 project and then copy the files from the repository.
For example the repository has the only controller with one action that can be reached with url
 http://localhost:8000/app/test/test (according to rule: /{bundle_name}/{controller}/{action})

1. Add a new service
```YAML
services:
     app.dynamicRouter.listener:
        class: AppBundle\EventListener\DynamicRouterListener
        arguments: ["@kernel", "@request_stack"]
        tags:
            - { name: kernel.event_listener, event: kernel.request, priority: 33 } // (kernel.request has priority 32)
```

2. Add our new listener class
https://github.com/mixtline/test-task-2/blob/master/src/AppBundle/EventListener/DynamicRouterListener.php

