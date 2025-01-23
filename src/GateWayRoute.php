<?php

namespace Annotation\Routing;

use Annotation\Routing\Contracts\GateWayRouteContract;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class GateWayRoute implements GateWayRouteContract
{
    public function gateWay(string $endpoint = 'gateway.do', Closure $action = null, Closure $version = null): \Illuminate\Routing\Route
    {
        return Route::get($endpoint, function (Request $request) use ($action, $version) {
            $name    = app()->call($action ?? function (Request $request) {
                return $request->request->get('action');
            });
            $version = app()->call($version ?? function (Request $request) {
                return $request->request->get('version');
            });
            // 创建 Symfony Request
            $subRequest = SymfonyRequest::create(
                $this->getRouteUriByName($name, $version),
                $request->getMethod(),
                $request->all(),
                $request->cookies->all(),
                $request->files->all(),
                $request->server->all(),
                $request->getContent()
            );
            // 将 SymfonyRequest 转换为 Request 实例
            $request = Request::createFromBase($subRequest);
            // 将新的 Request 实例绑定到服务容器
            app()->instance(Request::class, $request);
            //使用路由器来分发子请求
            return Route::dispatch($request);
        })->name('gateway');
    }

    protected function getRouteUriByName(string|null $name, string|null $version, string $default = '{fallbackPlaceholder}'): string
    {
        if (is_null($name)) {
            return $default;
        }

        $alias = collect(config('routing.alias', []))->get($version ?: '1.0.0', []);
        $name  = collect($alias)->reduce(function ($target, $value, $key) {
            return stripos($target, $key) === 0 ? str_ireplace($key, $value, $target) : $target;
        }, $name);

        return Route::getRoutes()->hasNamedRoute($name) ? Route::getRoutes()->getByName($name)->uri() : $default;
    }

}
