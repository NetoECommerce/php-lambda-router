# php-lambda-router #

Simple handler-based router middleware for the [php-lambda-runtime](https://github.com/NetoECommerce/php-lambda-runtime) library.

## Installation

Run `composer require netolabs/php-lambda-router netolabs/php-lambda-runtime` from the root of your project.

## Using the Router Middleware

The Router middleware is used to route our Lambda to a Controller and Action, much like an MVC framework would.
The difference being that instead of routing based on the request path, we use the Lambda handler name.
This allows us to have multiple Lambda functions inside the same repository and also allowing more reuse of code.

The handler name is expected in the format of "controller.action".
Internally, the router will attempt to load the controller and check if the action method exists.
If not, it will pass the request on to the next middleware in the queue; usually a fallback or a 404 middleware.

For example: if the handler name is helloworld.get, the router will attempt to load the `\App\Controller\HelloworldController` class and execute the method `getAction($request)`.

The default namespace is `\App\Controller\`, however this is configurable via constructor parameter.

## Example

### Adding the Router middleware

In this example we're using the Router to route our Lambda to a Controller and Action.
It includes an exception handler and a fallback which returns a 404 response if the route isn't matched.
In your `app.php` use the following code: 

    <?php
    $app = \Neto\Lambda\Application\AppFactory::create();
    $app->addMiddleware(new \Neto\Lambda\Middleware\ExceptionHandler(true))
        ->addMiddleware(new \Neto\Lambda\Middleware\HandlerRouter(getenv('_HANDLER')))
        ->addMiddleware(new \Neto\Lambda\Middleware\FileNotFound())
        ->run();

### Creating a Controller

Controllers are autoloaded using the Lambda handler name and must conform to the PSR-4 standard. 

    mkdir -p src/App/Controller
    touch src/App/Controller/AppController.php

src/App/Controller/AppController.php:

    <?php
    namespace App\Controller;

    use GuzzleHttp\Psr7\Response;
    use Neto\Lambda\Controller\AbstractController;

    class AppController extends AbstractController
    {
        public function helloworldAction(RequestInterface $request)
        {
            return new Response(200, [], '<h1>Hello world!</h1>');
        }
    }

### Send it

Now run it using `vendor/bin/invoke -h app.helloworld`.
You'll notice we are using "app.helloworld" as our handler name - this is used to resolve our controller class
(AppController) and action method (HelloworldAction).

### PSR-11 Containers and auto-wiring

Both the Router middleware and the AbstractController implement the ContainerAwareInterface.
If you'd like to use a DI container, you only need to inject it into the HandlerRouter either via constructor or method.
The container object (when set) will also be injected automatically into controllers during instantiation.   
Because this library uses PHP-DI's invoker, you can use typed parameters for your action methods in any order
and we will attempt to resolve them in your PSR-11 container using either the parameter type or the parameter name. 

## License

The MIT License (MIT). Please see the [License File](https://github.com/NetoECommerce/php-lambda-router/blob/master/LICENSE) for more information.
