<?php


use App\CentralLogics\helpers;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

if (!function_exists('response_formatter')) {
    function response_formatter($constant, $content = null, $errors = []): array
    {
        $constant = (array)$constant;
        $constant['content'] = $content;
        $constant['errors'] = $errors;
        return $constant;
    }
}

if (!function_exists('send_push_notification_to_device')) {
    function send_push_notification_to_device($fcm_token, $data)
    {
        /*https://fcm.googleapis.com/v1/projects/myproject-b5ae1/messages:send*/
        $key = Helpers::get_business_settings('push_notification_key');

        $url = "https://fcm.googleapis.com/fcm/send";
        $header = array("authorization: key=" . $key . "",
            "content-type: application/json"
        );

        $postdata = '{
            "to" : "' . $fcm_token . '",
            "mutable-content": "true",
            "data" : {
                "title":"' . $data['title'] . '",
                "body" : "' . $data['description'] . '",
                "image" : "' . $data['image'] . '",
                "is_read": 0
              },
             "notification" : {
                "title" :"' . $data['title'] . '",
                "body" : "' . $data['description'] . '",
                "image" : "' . $data['image'] . '",
                "title_loc_key":"' . $data['order_id'] . '",
                "is_read": 0,
                "icon" : "new",
                "sound" : "default"
              }
        }';

        $ch = curl_init();
        $timeout = 120;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        // Get URL content
        $result = curl_exec($ch);
        // close handle to release resources
        curl_close($ch);

        return $result;
    }
}

if (!function_exists('send_push_notification_to_topic')) {
    function send_push_notification_to_topic($data)
    {
        /*https://fcm.googleapis.com/v1/projects/myproject-b5ae1/messages:send*/
        $key = BusinessSetting::where(['key' => 'push_notification_key'])->first()->value;

        $url = "https://fcm.googleapis.com/fcm/send";
        $header = array("authorization: key=" . $key . "",
            "content-type: application/json"
        );

        $image = asset('storage/app/public/notification') . '/' . $data['image'];
        $postdata = '{
            "to" : "/topics/' . $data['receiver'] . '",
            "mutable-content": "true",
            "data" : {
                "title" :"' . $data['title'] . '",
                "body" : "' . $data['description'] . '",
                "image" : "' . $image . '",
                "is_read": 0
              },
              "notification" : {
                "title" :"' . $data['title'] . '",
                "body" : "' . $data['description'] . '",
                "image" : "' . $image . '",
                "is_read": 0,
                "icon" : "new",
                "sound" : "default"
              }
        }';

        $ch = curl_init();
        $timeout = 120;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        // Get URL content
        $result = curl_exec($ch);
        // close handle to release resources
        curl_close($ch);

        return $result;
    }
}

if (!function_exists('date_time_formatter')) {
    function date_time_formatter($datetime)
    {
        $time_zone = Helpers::get_business_settings('timezone') ?? 'UTC';

        try {
            return date('d-M-Y h:iA', strtotime($datetime->timezone($time_zone)->toDateTimeString()));

        } catch (\Exception $exception) {
            return date('d-M-Y h:iA', strtotime($datetime));
        }
    }
}

if (!function_exists('addon_published_status')) {
    function addon_published_status($module_name)
    {
        $is_published = 0;
        try {
            $full_data = include("Modules/{$module_name}/Addon/info.php");
            $is_published = $full_data['is_published'] == 1 ? 1 : 0;
            return $is_published;
        } catch (\Exception $exception) {
            return 0;
        }
    }
}

if (!function_exists('file_remover')) {
    function file_remover(string $dir, $image)
    {
        if (!isset($image)) return true;

        if (Storage::disk('public')->exists($dir . $image)) Storage::disk('public')->delete($dir . $image);

        return true;
    }
}

if (!function_exists('file_uploader')) {
    function file_uploader(string $dir, string $format, $image = null, $old_image = null)
    {
        if ($image == null) return $old_image ?? 'def.png';

        if (isset($old_image)) Storage::disk('public')->delete($dir . $old_image);

        $imageName = \Carbon\Carbon::now()->toDateString() . "-" . uniqid() . "." . $format;
        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }
        Storage::disk('public')->put($dir . $imageName, file_get_contents($image));

        return $imageName;
    }
}

if (!function_exists('change_text_color_or_bg')) {
    function change_text_color_or_bg($data)
    {
        // Replace ##text## with <span class="bg-primary text-white">text</span>
        $data = preg_replace('/##([^#]+)##/', '<span class="bg-primary text-white">$1</span>', $data);

        // Replace **text** with <span class="text-primary">text</span>
        $data = preg_replace('/\*\*([^*]+)\*\*/', '<span class="text-primary">$1</span>', $data);

        // Replace %% with </br>
        $data = str_replace('%%', '</br>', $data);

        // Replace @@text@@ with <b>text</b>
        $data = preg_replace('/@@([^@]+)@@/', '<b>$1</b>', $data);

        return $data;
    }
}


if (!function_exists('config_settings')) {
    function config_settings($key, $settings_type)
    {
        try {
            $config = DB::table('addon_settings')->where('key_name', $key)
                ->where('settings_type', $settings_type)->first();
        } catch (Exception $exception) {
            return null;
        }

        return (isset($config)) ? $config : null;
    }
}

if (!function_exists('format_number')) {
    function format_number($value) {
        $val = abs($value);

        if ($val >= 1000000000000) {
            $val = number_format($val / 1000000000000, 1) . ' T';
        } elseif ($val >= 1000000000) {
            $val = number_format($val / 1000000000, 1) . ' B';
        } elseif ($val >= 1000000) {
            $val = number_format($val / 1000000, 1) . ' M';
        } elseif ($val >= 1000) {
            $val = number_format($val / 1000, 1) . ' K';
        }

        return $val;
    }
}






