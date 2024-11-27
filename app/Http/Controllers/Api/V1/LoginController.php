<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\helpers;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLogHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Carbon\CarbonInterval;

class LoginController extends Controller
{
    public function __construct(
        private User $user,
        private UserLogHistory $userLogHistory
    ){}

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception 'f_name' => 'required',
     */
    public function customerLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'dial_country_code' => 'required',
            'phone' => 'required',
            'password' => 'required',
            
        ]);

      
        
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        
        

        $phone = $request->dial_country_code . $request->phone;
        $user = $this->user->customer()->where('phone', $phone)->first();

        if (!isset($user)) {
            return response()->json(response_formatter(AUTH_LOGIN_404, null, Helpers::error_processor($validator)), 404);
        }

        if (isset($user->is_active) && $user->is_active == false) {
            return response()->json(response_formatter(AUTH_BLOCK_LOGIN_403, null, Helpers::error_processor($validator)), 403);
        }

        $tempBlockTime = Helpers::get_business_settings('temporary_login_block_time') ?? 600; // seconds

        if ($user->is_temp_blocked) {
            if(isset($user->temp_block_time) && Carbon::parse($user->temp_block_time)->DiffInSeconds() <= $tempBlockTime){
                $time = $tempBlockTime - Carbon::parse($user->temp_block_time)->DiffInSeconds();
                $response = [
                    "response_code" => "auth_login_401",
                    "message" => translate('Your account is temporarily blocked. Please_try_again_after_'). CarbonInterval::seconds($time)->cascade()->forHumans(),
                ];
                return response()->json(response_formatter($response, null, null), 403);
            }

            $user->login_hit_count = 0;
            $user->is_temp_blocked = 0;
            $user->temp_block_time = null;
            $user->save();
        }

        if (!Hash::check($request['password'], $user['password'])) {
            self::updateUserHitCount($user);
            return response()->json(response_formatter(AUTH_LOGIN_401, null, Helpers::error_processor($validator)), 401);
        }

        if(isset($user->temp_block_time) && Carbon::parse($user->temp_block_time)->DiffInSeconds() <= $tempBlockTime){
            $time = $tempBlockTime - Carbon::parse($user->temp_block_time)->DiffInSeconds();

            $response = [
                "response_code" => "auth_login_401",
                "message" => translate('Try_again_after') . ' ' . CarbonInterval::seconds($time)->cascade()->forHumans()
            ];
            return response()->json(response_formatter($response, null, null), 403);
        }

        $logStatus = self::logUserHistory($request, $user->id);
        if(!$logStatus) {
            return response()->json(response_formatter(AUTH_LOGIN_400, null, Helpers::error_processor($validator)), 400);
        }

        $user->update(['last_active_at' => now()]);
        $user->AauthAcessToken()->delete();
        $token = $user->createToken('CustomerAuthToken')->accessToken;
        return response()->json(response_formatter(AUTH_LOGIN_200, $token, null), 200);
    }

    /**
     * @param $user
     * @return void
     */
    public function updateUserHitCount($user): void
    {
        $maxLoginHit = Helpers::get_business_settings('maximum_login_hit') ?? 5;

        $user->login_hit_count += 1;
        if ($user->login_hit_count >= $maxLoginHit) {
            $user->is_temp_blocked = 1;
            $user->temp_block_time = now();
        }
        $user->save();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function agentLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'dial_country_code' => 'required|string',
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, Helpers::error_processor($validator)), 400);
        }
        $phone = $request->dial_country_code . $request->phone;
        $user = $this->user->agent()->where('phone', $phone)->first();

        if (!isset($user)) {
            return response()->json(response_formatter(AUTH_LOGIN_404, null, Helpers::error_processor($validator)), 404);
        }

        if (isset($user->is_active) && $user->is_active == false) {
            return response()->json(response_formatter(AUTH_BLOCK_LOGIN_403, null, Helpers::error_processor($validator)), 403);
        }
        $tempBlockTime = Helpers::get_business_settings('temporary_login_block_time') ?? 600; // seconds

        if ($user->is_temp_blocked) {
            if(isset($user->temp_block_time) && Carbon::parse($user->temp_block_time)->DiffInSeconds() <= $tempBlockTime){
                $time = $tempBlockTime - Carbon::parse($user->temp_block_time)->DiffInSeconds();
                $response = [
                    "response_code" => "auth_login_401",
                    "message" => translate('Your account is temporarily blocked. Please_try_again_after_'). CarbonInterval::seconds($time)->cascade()->forHumans(),
                ];
                return response()->json(response_formatter($response, null, null), 403);
            }

            $user->login_hit_count = 0;
            $user->is_temp_blocked = 0;
            $user->temp_block_time = null;
            $user->save();
        }

        if (!Hash::check($request['password'], $user['password'])) {
            self::updateUserHitCount($user);
            return response()->json(response_formatter(AUTH_LOGIN_401, null, Helpers::error_processor($validator)), 401);
        }

        if(isset($user->temp_block_time) && Carbon::parse($user->temp_block_time)->DiffInSeconds() <= $tempBlockTime){
            $time = $tempBlockTime - Carbon::parse($user->temp_block_time)->DiffInSeconds();

            $response = [
                "response_code" => "auth_login_401",
                "message" => translate('Try_again_after') . ' ' . CarbonInterval::seconds($time)->cascade()->forHumans()
            ];
            return response()->json(response_formatter($response, null, null), 403);
        }

        $logStatus = self::logUserHistory($request, $user->id);
        if(!$logStatus) {
            return response()->json(response_formatter(AUTH_LOGIN_400, null, Helpers::error_processor($validator)), 400);
        }

        $user->update(['last_active_at' => now()]);
        $user->AauthAcessToken()->delete();
        $token = $user->createToken('AgentAuthToken')->accessToken;
        return response()->json(response_formatter(AUTH_LOGIN_200, $token, null), 200);
    }

    /**
     * @param $request
     * @param $user_id
     * @return bool
     */
    public function logUserHistory($request, $user_id): bool
    {
        $ipAddress = $request->ip();
        $deviceId = $request->header('device-id');
        $browser = $request->header('browser');
        $os = $request->header('os');
        $deviceModel = $request->header('device-model');

        if($deviceId == '' || $os == '' || $deviceModel == '') {
            return false;
        }

        DB::beginTransaction();
        try {
            $this->userLogHistory->where('user_id', $user_id)->update(['is_active' => 0]);

            $this->userLogHistory->create(
                [
                    'ip_address' => $ipAddress,
                    'device_id' => $deviceId,
                    'browser' => $browser,
                    'os' => $os,
                    'device_model' => $deviceModel,
                    'user_id' => $user_id,
                ]
            );
            DB::commit();

        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }

        return true;
    }
}
