<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function __construct(
        private Banner $banner
    ){}


    /**
     * @param Request $request
     * @return mixed
     */
    public function getCustomerBanner(Request $request): mixed
    {
        $banners = $this->banner->select('title', 'image', 'url', 'receiver')->customerAndAll()->active()->get();
        return $banners;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getAgentBanner(Request $request): mixed
    {
        $banners = $this->banner->select('title', 'image', 'url', 'receiver')->agentAndAll()->active()->get();
        return $banners;
    }
}
