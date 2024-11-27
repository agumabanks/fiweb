<?php
use App\Models\BusinessSetting;
$company_phone =BusinessSetting::where('key', 'phone')->first()->value;
$company_email =BusinessSetting::where('key', 'email')->first()->value;
$company_name =BusinessSetting::where('key', 'business_name')->first()->value;
$logo =BusinessSetting::where('key', 'logo')->first()->value;
$company_address =BusinessSetting::where('key', 'address')->first()->value;
$company_mobile_logo = $logo;
?>
<!DOCTYPE html>
<html>
<head>

  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>{{translate('Reply_form_'.$company_name)}}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <style type="text/css">

  @import url('https://fonts.googleapis.com/css2?family=Roboto&display=swap');

   body{
    font-family: 'Roboto', sans-serif;
   }
  body,
  table,
  td,
  a {
    -ms-text-size-adjust: 100%; /* 1 */
    -webkit-text-size-adjust: 100%; /* 2 */
  }

  table,
  td {
    mso-table-rspace: 0pt;
    mso-table-lspace: 0pt;

  }

  img {
    -ms-interpolation-mode: bicubic;
  }

  a[x-apple-data-detectors] {
    font-family: inherit !important;
    font-size: inherit !important;
    font-weight: inherit !important;
    line-height: inherit !important;
    color: inherit !important;
    text-decoration: none !important;
  }

  table {
    border-collapse: collapse !important;
  }

  a {
    color: #1a82e2;
  }

  img {
    height: auto;
    line-height: 100%;
    text-decoration: none;
    border: 0;
    outline: none;
  }

  </style>

</head>
<body style="background-color: #ececec;margin:0;padding:0">

<div style="width:650px;margin:auto; background-color:#ececec;height:50px;">

</div>
<div style="width:650px;margin:auto; background-color:white;margin-top:100px;
            padding-top:40px;padding-bottom:40px;border-radius: 3px;">
    <table style="background-color: rgb(255, 255, 255);width: 90%;margin:auto;height:72px; border-bottom:1px ridge;">
        <tbody>
            <tr>
                <td>
                    <h3 style="color:green;">{{translate('Dear')}}, {{ $data['name'] }}</h3>
                </td>

                <td>
                    <div style="text-align: end; margin-inline-end:15px;">
                        <img style="max-width:250px;border:0;" src="{{asset('/storage/app/public/business/'.$logo)}}" title=""
                            class="sitelogo" width="60%"  alt="{{ translate('logo') }}"/>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <div style="background-color: rgb(248, 248, 248); width: 90%;margin:auto;margin-top:30px;">
        <div style="padding:20px;">
            <p>{!! $data['body'] ?? '' !!}</p>
        </div>
    </div>
</div>

<div style="padding:5px;width:650px;margin:auto;margin-top:5px; margin-bottom:50px;">
    <table style="margin:auto;width:90%; color:#777777;">
        <tbody style="text-align: center;">
            <tr>
                <th >
                    <div style="font-weight: 400;font-size: 11px;line-height: 22px;color: #242A30;"><span style="margin-inline-end:5px;"> <a href="tel:{{$company_phone}}" style="text-decoration: none; color: inherit;">{{translate('phone')}}: {{$company_phone}}</a></span> <span><a href="mailto:{{$company_email}}" style="text-decoration: none; color: inherit;">{{translate('email')}}: {{$company_email}}</a></span></div>
                    <div style="font-weight: 400;font-size: 11px;line-height: 22px;color: #242A30;">{{$company_address}}</div>
                    <span style="font-weight: 400;font-size: 10px;line-height: 22px;color: #242A30;">{{translate('All_copy_right_reserved',['year'=>date('Y'),'title'=>$company_name])}}</span>
                </th>
            </tr>

        </tbody>
    </table>

</div>
</body>
</html>
