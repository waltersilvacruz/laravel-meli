<?php

namespace WebDEV\Meli\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use WebDEV\Meli\Services\MeliApiService;

class MeliController extends Controller
{
    /**
     * Connect to ML
     * @param ViewFactory $view
     * @param string $state
     * @return View
     */
    public function connect(ViewFactory $view, string $state): View {
        $service = new MeliApiService($state);
        $redirectUri = route('meli.token');
        $link = $service->getAuthUrl($redirectUri);
        return $view->make('meli::connect', compact('link'));
    }

    /**
     * Disconnect from ML
     * @param ViewFactory $view
     * @param string $state
     * @return RedirectResponse
     */
    public function disconnect(ViewFactory $view, string $state): RedirectResponse {
        $service = new MeliApiService($state);
        $service->disconnect();
        return redirect(route(config('meli.redirect_route')));
    }

    /**
     * Process authorization token
     * @throws Exception
     */
    public function token(Request $request): RedirectResponse {
        $code = $request->get('code');
        $state = $request->get('state');
        $service = new MeliApiService($state);
        $redirectUri = route('meli.token');
        $authorize = $service->authorize($code, $redirectUri);
        if($authorize->httpCode != 200) {
            throw new Exception($authorize->body->message);
        }
        return redirect(route(config('meli.redirect_route')));
    }
}
