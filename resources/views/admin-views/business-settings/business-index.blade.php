@extends('layouts.admin.app')

@section('title', translate('Settings'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 pb-2">
            <img width="24" src="{{asset('public/assets/admin/img/media/business-setup.png')}}" alt="{{ translate('business_setup') }}">
            <h2 class="page-header-title">{{translate('Business Setup')}}</h2>
        </div>

        <div class="inline-page-menu my-4">
            @include('admin-views.business-settings.partial._business-setup-tabs')
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 text-capitalize d-flex align-items-center gap-1">
                    <i class="tio-briefcase"></i> {{translate('Business Information')}}
                </h5>
            </div>
            <div class="card-body">
                    <form action="{{route('admin.business-settings.update-setup')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            @php($name=\App\Models\BusinessSetting::where('key','business_name')->first())
                            <div class="col-sm-6 col-xl-4">
                                <div class="form-group">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('business')}} {{translate('name')}}</label>
                                    <input type="text" name="restaurant_name" value="{{$name->value??''}}" class="form-control"
                                        placeholder="{{translate('New Business')}}" required>
                                </div>
                            </div>
                            @php($currencyCode=\App\Models\BusinessSetting::where('key','currency')->first())
                            <div class="col-sm-6 col-xl-4">
                                <div class="form-group">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('currency')}}</label>
                                    <select name="currency" class="form-control js-select2-custom">
                                        @foreach(\App\Models\Currency::orderBy('currency_code')->get() as $currency)
                                            <option
                                                value="{{$currency['currency_code']}}" {{$currencyCode?($currencyCode->value==$currency['currency_code']?'selected':''):''}}>
                                                {{$currency['currency_code']}} ( {{$currency['currency_symbol']}} )
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @php($currencySymbolPosition=\App\Models\BusinessSetting::where('key','currency_symbol_position')->first())
                            <div class="col-sm-6 col-xl-4">
                                <div class="form-group">
                                    <label class="input-label text-capitalize" for="currency_symbol_position">{{translate('currency_symbol_positon')}}</label>
                                    <select name="currency_symbol_position" class="form-control js-select2-custom" id="currency_symbol_position">
                                        <option value="left" {{$currencySymbolPosition?($currencySymbolPosition->value=='left'?'selected':''):''}}>
                                            {{translate('left')}} ({{\App\CentralLogics\Helpers::currency_symbol()}}123)
                                        </option>
                                        <option value="right" {{$currencySymbolPosition?($currencySymbolPosition->value=='right'?'selected':''):''}}>
                                            {{translate('right')}} (123{{\App\CentralLogics\Helpers::currency_symbol()}})
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-4">
                                @php($paginationLimit=\App\CentralLogics\Helpers::get_business_settings('pagination_limit')??25)
                                <div class="form-group">
                                    <label class="input-label">{{translate('pagination')}} {{translate('settings')}}</label>
                                    <input type="number" value="{{$paginationLimit}}" min="1"
                                        name="pagination_limit" class="form-control" placeholder="25">
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-4">
                                <div class="form-group">
                                    <label class="input-label text-capitalize" for="country">{{translate('country')}}</label>
                                    <select id="country" name="country" class="form-control  js-select2-custom">
                                        <option value="" selected disabled>{{translate('Select Country')}}</option>
                                        <option value="AF">Afghanistan</option>
                                        <option value="AX">Åland Islands</option>
                                        <option value="AL">Albania</option>
                                        <option value="DZ">Algeria</option>
                                        <option value="AS">American Samoa</option>
                                        <option value="AD">Andorra</option>
                                        <option value="AO">Angola</option>
                                        <option value="AI">Anguilla</option>
                                        <option value="AQ">Antarctica</option>
                                        <option value="AG">Antigua and Barbuda</option>
                                        <option value="AR">Argentina</option>
                                        <option value="AM">Armenia</option>
                                        <option value="AW">Aruba</option>
                                        <option value="AU">Australia</option>
                                        <option value="AT">Austria</option>
                                        <option value="AZ">Azerbaijan</option>
                                        <option value="BS">Bahamas</option>
                                        <option value="BH">Bahrain</option>
                                        <option value="BD">Bangladesh</option>
                                        <option value="BB">Barbados</option>
                                        <option value="BY">Belarus</option>
                                        <option value="BE">Belgium</option>
                                        <option value="BZ">Belize</option>
                                        <option value="BJ">Benin</option>
                                        <option value="BM">Bermuda</option>
                                        <option value="BT">Bhutan</option>
                                        <option value="BO">Bolivia, Plurinational State of</option>
                                        <option value="BQ">Bonaire, Sint Eustatius and Saba</option>
                                        <option value="BA">Bosnia and Herzegovina</option>
                                        <option value="BW">Botswana</option>
                                        <option value="BV">Bouvet Island</option>
                                        <option value="BR">Brazil</option>
                                        <option value="IO">British Indian Ocean Territory</option>
                                        <option value="BN">Brunei Darussalam</option>
                                        <option value="BG">Bulgaria</option>
                                        <option value="BF">Burkina Faso</option>
                                        <option value="BI">Burundi</option>
                                        <option value="KH">Cambodia</option>
                                        <option value="CM">Cameroon</option>
                                        <option value="CA">Canada</option>
                                        <option value="CV">Cape Verde</option>
                                        <option value="KY">Cayman Islands</option>
                                        <option value="CF">Central African Republic</option>
                                        <option value="TD">Chad</option>
                                        <option value="CL">Chile</option>
                                        <option value="CN">China</option>
                                        <option value="CX">Christmas Island</option>
                                        <option value="CC">Cocos (Keeling) Islands</option>
                                        <option value="CO">Colombia</option>
                                        <option value="KM">Comoros</option>
                                        <option value="CG">Congo</option>
                                        <option value="CD">Congo, the Democratic Republic of the</option>
                                        <option value="CK">Cook Islands</option>
                                        <option value="CR">Costa Rica</option>
                                        <option value="CI">Côte d'Ivoire</option>
                                        <option value="HR">Croatia</option>
                                        <option value="CU">Cuba</option>
                                        <option value="CW">Curaçao</option>
                                        <option value="CY">Cyprus</option>
                                        <option value="CZ">Czech Republic</option>
                                        <option value="DK">Denmark</option>
                                        <option value="DJ">Djibouti</option>
                                        <option value="DM">Dominica</option>
                                        <option value="DO">Dominican Republic</option>
                                        <option value="EC">Ecuador</option>
                                        <option value="EG">Egypt</option>
                                        <option value="SV">El Salvador</option>
                                        <option value="GQ">Equatorial Guinea</option>
                                        <option value="ER">Eritrea</option>
                                        <option value="EE">Estonia</option>
                                        <option value="ET">Ethiopia</option>
                                        <option value="FK">Falkland Islands (Malvinas)</option>
                                        <option value="FO">Faroe Islands</option>
                                        <option value="FJ">Fiji</option>
                                        <option value="FI">Finland</option>
                                        <option value="FR">France</option>
                                        <option value="GF">French Guiana</option>
                                        <option value="PF">French Polynesia</option>
                                        <option value="TF">French Southern Territories</option>
                                        <option value="GA">Gabon</option>
                                        <option value="GM">Gambia</option>
                                        <option value="GE">Georgia</option>
                                        <option value="DE">Germany</option>
                                        <option value="GH">Ghana</option>
                                        <option value="GI">Gibraltar</option>
                                        <option value="GR">Greece</option>
                                        <option value="GL">Greenland</option>
                                        <option value="GD">Grenada</option>
                                        <option value="GP">Guadeloupe</option>
                                        <option value="GU">Guam</option>
                                        <option value="GT">Guatemala</option>
                                        <option value="GG">Guernsey</option>
                                        <option value="GN">Guinea</option>
                                        <option value="GW">Guinea-Bissau</option>
                                        <option value="GY">Guyana</option>
                                        <option value="HT">Haiti</option>
                                        <option value="HM">Heard Island and McDonald Islands</option>
                                        <option value="VA">Holy See (Vatican City State)</option>
                                        <option value="HN">Honduras</option>
                                        <option value="HK">Hong Kong</option>
                                        <option value="HU">Hungary</option>
                                        <option value="IS">Iceland</option>
                                        <option value="IN">India</option>
                                        <option value="ID">Indonesia</option>
                                        <option value="IR">Iran, Islamic Republic of</option>
                                        <option value="IQ">Iraq</option>
                                        <option value="IE">Ireland</option>
                                        <option value="IM">Isle of Man</option>
                                        <option value="IL">Israel</option>
                                        <option value="IT">Italy</option>
                                        <option value="JM">Jamaica</option>
                                        <option value="JP">Japan</option>
                                        <option value="JE">Jersey</option>
                                        <option value="JO">Jordan</option>
                                        <option value="KZ">Kazakhstan</option>
                                        <option value="KE">Kenya</option>
                                        <option value="KI">Kiribati</option>
                                        <option value="KP">Korea, Democratic People's Republic of</option>
                                        <option value="KR">Korea, Republic of</option>
                                        <option value="KW">Kuwait</option>
                                        <option value="KG">Kyrgyzstan</option>
                                        <option value="LA">Lao People's Democratic Republic</option>
                                        <option value="LV">Latvia</option>
                                        <option value="LB">Lebanon</option>
                                        <option value="LS">Lesotho</option>
                                        <option value="LR">Liberia</option>
                                        <option value="LY">Libya</option>
                                        <option value="LI">Liechtenstein</option>
                                        <option value="LT">Lithuania</option>
                                        <option value="LU">Luxembourg</option>
                                        <option value="MO">Macao</option>
                                        <option value="MK">Macedonia, the former Yugoslav Republic of</option>
                                        <option value="MG">Madagascar</option>
                                        <option value="MW">Malawi</option>
                                        <option value="MY">Malaysia</option>
                                        <option value="MV">Maldives</option>
                                        <option value="ML">Mali</option>
                                        <option value="MT">Malta</option>
                                        <option value="MH">Marshall Islands</option>
                                        <option value="MQ">Martinique</option>
                                        <option value="MR">Mauritania</option>
                                        <option value="MU">Mauritius</option>
                                        <option value="YT">Mayotte</option>
                                        <option value="MX">Mexico</option>
                                        <option value="FM">Micronesia, Federated States of</option>
                                        <option value="MD">Moldova, Republic of</option>
                                        <option value="MC">Monaco</option>
                                        <option value="MN">Mongolia</option>
                                        <option value="ME">Montenegro</option>
                                        <option value="MS">Montserrat</option>
                                        <option value="MA">Morocco</option>
                                        <option value="MZ">Mozambique</option>
                                        <option value="MM">Myanmar</option>
                                        <option value="NA">Namibia</option>
                                        <option value="NR">Nauru</option>
                                        <option value="NP">Nepal</option>
                                        <option value="NL">Netherlands</option>
                                        <option value="NC">New Caledonia</option>
                                        <option value="NZ">New Zealand</option>
                                        <option value="NI">Nicaragua</option>
                                        <option value="NE">Niger</option>
                                        <option value="NG">Nigeria</option>
                                        <option value="NU">Niue</option>
                                        <option value="NF">Norfolk Island</option>
                                        <option value="MP">Northern Mariana Islands</option>
                                        <option value="NO">Norway</option>
                                        <option value="OM">Oman</option>
                                        <option value="PK">Pakistan</option>
                                        <option value="PW">Palau</option>
                                        <option value="PS">Palestinian Territory, Occupied</option>
                                        <option value="PA">Panama</option>
                                        <option value="PG">Papua New Guinea</option>
                                        <option value="PY">Paraguay</option>
                                        <option value="PE">Peru</option>
                                        <option value="PH">Philippines</option>
                                        <option value="PN">Pitcairn</option>
                                        <option value="PL">Poland</option>
                                        <option value="PT">Portugal</option>
                                        <option value="PR">Puerto Rico</option>
                                        <option value="QA">Qatar</option>
                                        <option value="RE">Réunion</option>
                                        <option value="RO">Romania</option>
                                        <option value="RU">Russian Federation</option>
                                        <option value="RW">Rwanda</option>
                                        <option value="BL">Saint Barthélemy</option>
                                        <option value="SH">Saint Helena, Ascension and Tristan da Cunha</option>
                                        <option value="KN">Saint Kitts and Nevis</option>
                                        <option value="LC">Saint Lucia</option>
                                        <option value="MF">Saint Martin (French part)</option>
                                        <option value="PM">Saint Pierre and Miquelon</option>
                                        <option value="VC">Saint Vincent and the Grenadines</option>
                                        <option value="WS">Samoa</option>
                                        <option value="SM">San Marino</option>
                                        <option value="ST">Sao Tome and Principe</option>
                                        <option value="SA">Saudi Arabia</option>
                                        <option value="SN">Senegal</option>
                                        <option value="RS">Serbia</option>
                                        <option value="SC">Seychelles</option>
                                        <option value="SL">Sierra Leone</option>
                                        <option value="SG">Singapore</option>
                                        <option value="SX">Sint Maarten (Dutch part)</option>
                                        <option value="SK">Slovakia</option>
                                        <option value="SI">Slovenia</option>
                                        <option value="SB">Solomon Islands</option>
                                        <option value="SO">Somalia</option>
                                        <option value="ZA">South Africa</option>
                                        <option value="GS">South Georgia and the South Sandwich Islands</option>
                                        <option value="SS">South Sudan</option>
                                        <option value="ES">Spain</option>
                                        <option value="LK">Sri Lanka</option>
                                        <option value="SD">Sudan</option>
                                        <option value="SR">Suriname</option>
                                        <option value="SJ">Svalbard and Jan Mayen</option>
                                        <option value="SZ">Swaziland</option>
                                        <option value="SE">Sweden</option>
                                        <option value="CH">Switzerland</option>
                                        <option value="SY">Syrian Arab Republic</option>
                                        <option value="TW">Taiwan, Province of China</option>
                                        <option value="TJ">Tajikistan</option>
                                        <option value="TZ">Tanzania, United Republic of</option>
                                        <option value="TH">Thailand</option>
                                        <option value="TL">Timor-Leste</option>
                                        <option value="TG">Togo</option>
                                        <option value="TK">Tokelau</option>
                                        <option value="TO">Tonga</option>
                                        <option value="TT">Trinidad and Tobago</option>
                                        <option value="TN">Tunisia</option>
                                        <option value="TR">Turkey</option>
                                        <option value="TM">Turkmenistan</option>
                                        <option value="TC">Turks and Caicos Islands</option>
                                        <option value="TV">Tuvalu</option>
                                        <option value="UG">Uganda</option>
                                        <option value="UA">Ukraine</option>
                                        <option value="AE">United Arab Emirates</option>
                                        <option value="GB">United Kingdom</option>
                                        <option value="US">United States</option>
                                        <option value="UM">United States Minor Outlying Islands</option>
                                        <option value="UY">Uruguay</option>
                                        <option value="UZ">Uzbekistan</option>
                                        <option value="VU">Vanuatu</option>
                                        <option value="VE">Venezuela, Bolivarian Republic of</option>
                                        <option value="VN">Viet Nam</option>
                                        <option value="VG">Virgin Islands, British</option>
                                        <option value="VI">Virgin Islands, U.S.</option>
                                        <option value="WF">Wallis and Futuna</option>
                                        <option value="EH">Western Sahara</option>
                                        <option value="YE">Yemen</option>
                                        <option value="ZM">Zambia</option>
                                        <option value="ZW">Zimbabwe</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-4">
                                @php($timeZone=\App\Models\BusinessSetting::where('key','timezone')->first())
                                @php($timeZone=$timeZone?$timeZone->value:0)
                                <div class="form-group">
                                    <label class="input-label text-capitalize">{{translate('time')}} {{translate('zone')}}</label>
                                    <select name="timezone" class="form-control js-select2-custom">
                                        <option value="UTC" {{$timeZone?($timeZone==''?'selected':''):''}}>UTC</option>
                                        <option value="Etc/GMT+12"  {{$timeZone?($timeZone=='Etc/GMT+12'?'selected':''):''}}>(GMT-12:00) International Date Line West</option>
                                        <option value="Pacific/Midway"  {{$timeZone?($timeZone=='Pacific/Midway'?'selected':''):''}}>(GMT-11:00) Midway Island, Samoa</option>
                                        <option value="Pacific/Honolulu"  {{$timeZone?($timeZone=='Pacific/Honolulu'?'selected':''):''}}>(GMT-10:00) Hawaii</option>
                                        <option value="US/Alaska"  {{$timeZone?($timeZone=='US/Alaska'?'selected':''):''}}>(GMT-09:00) Alaska</option>
                                        <option value="America/Los_Angeles"  {{$timeZone?($timeZone=='America/Los_Angeles'?'selected':''):''}}>(GMT-08:00) Pacific Time (US & Canada)</option>
                                        <option value="America/Tijuana"  {{$timeZone?($timeZone=='America/Tijuana'?'selected':''):''}}>(GMT-08:00) Tijuana, Baja California</option>
                                        <option value="US/Arizona"  {{$timeZone?($timeZone=='US/Arizona'?'selected':''):''}}>(GMT-07:00) Arizona</option>
                                        <option value="America/Chihuahua"  {{$timeZone?($timeZone=='America/Chihuahua'?'selected':''):''}}>(GMT-07:00) Chihuahua, La Paz, Mazatlan</option>
                                        <option value="US/Mountain"  {{$timeZone?($timeZone=='US/Mountain'?'selected':''):''}}>(GMT-07:00) Mountain Time (US & Canada)</option>
                                        <option value="America/Managua"  {{$timeZone?($timeZone=='America/Managua'?'selected':''):''}}>(GMT-06:00) Central America</option>
                                        <option value="US/Central"  {{$timeZone?($timeZone=='US/Central'?'selected':''):''}}>(GMT-06:00) Central Time (US & Canada)</option>
                                        <option value="America/Mexico_City"  {{$timeZone?($timeZone=='America/Mexico_City'?'selected':''):''}}>(GMT-06:00) Guadalajara, Mexico City, Monterrey</option>
                                        <option value="Canada/Saskatchewan"  {{$timeZone?($timeZone=='Canada/Saskatchewan'?'selected':''):''}}>(GMT-06:00) Saskatchewan</option>
                                        <option value="America/Bogota"  {{$timeZone?($timeZone=='America/Bogota'?'selected':''):''}}>(GMT-05:00) Bogota, Lima, Quito, Rio Branco</option>
                                        <option value="US/Eastern"  {{$timeZone?($timeZone=='US/Eastern'?'selected':''):''}}>(GMT-05:00) Eastern Time (US & Canada)</option>
                                        <option value="US/East-Indiana"  {{$timeZone?($timeZone=='US/East-Indiana'?'selected':''):''}}>(GMT-05:00) Indiana (East)</option>
                                        <option value="Canada/Atlantic"  {{$timeZone?($timeZone=='Canada/Atlantic'?'selected':''):''}}>(GMT-04:00) Atlantic Time (Canada)</option>
                                        <option value="America/Caracas"  {{$timeZone?($timeZone=='America/Caracas'?'selected':''):''}}>(GMT-04:00) Caracas, La Paz</option>
                                        <option value="America/Manaus"  {{$timeZone?($timeZone=='America/Manaus'?'selected':''):''}}>(GMT-04:00) Manaus</option>
                                        <option value="America/Santiago"  {{$timeZone?($timeZone=='America/Santiago'?'selected':''):''}}>(GMT-04:00) Santiago</option>
                                        <option value="Canada/Newfoundland"  {{$timeZone?($timeZone=='Canada/Newfoundland'?'selected':''):''}}>(GMT-03:30) Newfoundland</option>
                                        <option value="America/Sao_Paulo"  {{$timeZone?($timeZone=='America/Sao_Paulo'?'selected':''):''}}>(GMT-03:00) Brasilia</option>
                                        <option value="America/Argentina/Buenos_Aires"  {{$timeZone?($timeZone=='America/Argentina/Buenos_Aires'?'selected':''):''}}>(GMT-03:00) Buenos Aires, Georgetown</option>
                                        <option value="America/Godthab"  {{$timeZone?($timeZone=='America/Godthab'?'selected':''):''}}>(GMT-03:00) Greenland</option>
                                        <option value="America/Montevideo"  {{$timeZone?($timeZone=='America/Montevideo'?'selected':''):''}}>(GMT-03:00) Montevideo</option>
                                        <option value="America/Noronha"  {{$timeZone?($timeZone=='America/Noronha'?'selected':''):''}}>(GMT-02:00) Mid-Atlantic</option>
                                        <option value="Atlantic/Cape_Verde"  {{$timeZone?($timeZone=='Atlantic/Cape_Verde'?'selected':''):''}}>(GMT-01:00) Cape Verde Is.</option>
                                        <option value="Atlantic/Azores"  {{$timeZone?($timeZone=='Atlantic/Azores'?'selected':''):''}}>(GMT-01:00) Azores</option>
                                        <option value="Africa/Casablanca"  {{$timeZone?($timeZone=='Africa/Casablanca'?'selected':''):''}}>(GMT+00:00) Casablanca, Monrovia, Reykjavik</option>
                                        <option value="Etc/Greenwich"  {{$timeZone?($timeZone=='Etc/Greenwich'?'selected':''):''}}>(GMT+00:00) Greenwich Mean Time : Dublin, Edinburgh, Lisbon, London</option>
                                        <option value="Europe/Amsterdam"  {{$timeZone?($timeZone=='Europe/Amsterdam'?'selected':''):''}}>(GMT+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna</option>
                                        <option value="Europe/Belgrade"  {{$timeZone?($timeZone=='Europe/Belgrade'?'selected':''):''}}>(GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague</option>
                                        <option value="Europe/Brussels"  {{$timeZone?($timeZone=='Europe/Brussels'?'selected':''):''}}>(GMT+01:00) Brussels, Copenhagen, Madrid, Paris</option>
                                        <option value="Europe/Sarajevo"  {{$timeZone?($timeZone=='Europe/Sarajevo'?'selected':''):''}}>(GMT+01:00) Sarajevo, Skopje, Warsaw, Zagreb</option>
                                        <option value="Africa/Lagos"  {{$timeZone?($timeZone=='Africa/Lagos'?'selected':''):''}}>(GMT+01:00) West Central Africa</option>
                                        <option value="Asia/Amman"  {{$timeZone?($timeZone=='Asia/Amman'?'selected':''):''}}>(GMT+02:00) Amman</option>
                                        <option value="Europe/Athens"  {{$timeZone?($timeZone=='Europe/Athens'?'selected':''):''}}>(GMT+02:00) Athens, Bucharest, Istanbul</option>
                                        <option value="Asia/Beirut"  {{$timeZone?($timeZone=='Asia/Beirut'?'selected':''):''}}>(GMT+02:00) Beirut</option>
                                        <option value="Africa/Cairo"  {{$timeZone?($timeZone=='Africa/Cairo'?'selected':''):''}}>(GMT+02:00) Cairo</option>
                                        <option value="Africa/Harare"  {{$timeZone?($timeZone=='Africa/Harare'?'selected':''):''}}>(GMT+02:00) Harare, Pretoria</option>
                                        <option value="Europe/Helsinki"  {{$timeZone?($timeZone=='Europe/Helsinki'?'selected':''):''}}>(GMT+02:00) Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius</option>
                                        <option value="Asia/Jerusalem"  {{$timeZone?($timeZone=='Asia/Jerusalem'?'selected':''):''}}>(GMT+02:00) Jerusalem</option>
                                        <option value="Europe/Minsk"  {{$timeZone?($timeZone=='Europe/Minsk'?'selected':''):''}}>(GMT+02:00) Minsk</option>
                                        <option value="Africa/Windhoek"  {{$timeZone?($timeZone=='Africa/Windhoek'?'selected':''):''}}>(GMT+02:00) Windhoek</option>
                                        <option value="Asia/Kuwait"  {{$timeZone?($timeZone=='Asia/Kuwait'?'selected':''):''}}>(GMT+03:00) Kuwait, Riyadh, Baghdad</option>
                                        <option value="Europe/Moscow"  {{$timeZone?($timeZone=='Europe/Moscow'?'selected':''):''}}>(GMT+03:00) Moscow, St. Petersburg, Volgograd</option>
                                        <option value="Africa/Nairobi"  {{$timeZone?($timeZone=='Africa/Nairobi'?'selected':''):''}}>(GMT+03:00) Nairobi</option>
                                        <option value="Asia/Tbilisi"  {{$timeZone?($timeZone=='Asia/Tbilisi'?'selected':''):''}}>(GMT+03:00) Tbilisi</option>
                                        <option value="Asia/Tehran"  {{$timeZone?($timeZone=='Asia/Tehran'?'selected':''):''}}>(GMT+03:30) Tehran</option>
                                        <option value="Asia/Muscat"  {{$timeZone?($timeZone=='Asia/Muscat'?'selected':''):''}}>(GMT+04:00) Abu Dhabi, Muscat</option>
                                        <option value="Asia/Baku"  {{$timeZone?($timeZone=='Asia/Baku'?'selected':''):''}}>(GMT+04:00) Baku</option>
                                        <option value="Asia/Yerevan"  {{$timeZone?($timeZone=='Asia/Yerevan'?'selected':''):''}}>(GMT+04:00) Yerevan</option>
                                        <option value="Asia/Kabul"  {{$timeZone?($timeZone=='Asia/Kabul'?'selected':''):''}}>(GMT+04:30) Kabul</option>
                                        <option value="Asia/Yekaterinburg"  {{$timeZone?($timeZone=='Asia/Yekaterinburg'?'selected':''):''}}>(GMT+05:00) Yekaterinburg</option>
                                        <option value="Asia/Karachi"  {{$timeZone?($timeZone=='Asia/Karachi'?'selected':''):''}}>(GMT+05:00) Islamabad, Karachi, Tashkent</option>
                                        <option value="Asia/Calcutta"  {{$timeZone?($timeZone=='Asia/Calcutta'?'selected':''):''}}>(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi</option>
                                        <option value="Asia/Katmandu"  {{$timeZone?($timeZone=='Asia/Katmandu'?'selected':''):''}}>(GMT+05:45) Kathmandu</option>
                                        <option value="Asia/Almaty"  {{$timeZone?($timeZone=='Asia/Almaty'?'selected':''):''}}>(GMT+06:00) Almaty, Novosibirsk</option>
                                        <option value="Asia/Dhaka"  {{$timeZone?($timeZone=='Asia/Dhaka'?'selected':''):''}}>(GMT+06:00) Astana, Dhaka</option>
                                        <option value="Asia/Rangoon"  {{$timeZone?($timeZone=='Asia/Rangoon'?'selected':''):''}}>(GMT+06:30) Yangon (Rangoon)</option>
                                        <option value="Asia/Bangkok"  {{$timeZone?($timeZone=='"Asia/Bangkok'?'selected':''):''}}>(GMT+07:00) Bangkok, Hanoi, Jakarta</option>
                                        <option value="Asia/Krasnoyarsk"  {{$timeZone?($timeZone=='Asia/Krasnoyarsk'?'selected':''):''}}>(GMT+07:00) Krasnoyarsk</option>
                                        <option value="Asia/Hong_Kong"  {{$timeZone?($timeZone=='Asia/Hong_Kong'?'selected':''):''}}>(GMT+08:00) Beijing, Chongqing, Hong Kong, Urumqi</option>
                                        <option value="Asia/Kuala_Lumpur"  {{$timeZone?($timeZone=='Asia/Kuala_Lumpur'?'selected':''):''}}>(GMT+08:00) Kuala Lumpur, Singapore</option>
                                        <option value="Asia/Irkutsk"  {{$timeZone?($timeZone=='Asia/Irkutsk'?'selected':''):''}}>(GMT+08:00) Irkutsk, Ulaan Bataar</option>
                                        <option value="Australia/Perth"  {{$timeZone?($timeZone=='Australia/Perth'?'selected':''):''}}>(GMT+08:00) Perth</option>
                                        <option value="Asia/Taipei"  {{$timeZone?($timeZone=='Asia/Taipei'?'selected':''):''}}>(GMT+08:00) Taipei</option>
                                        <option value="Asia/Tokyo"  {{$timeZone?($timeZone=='Asia/Tokyo'?'selected':''):''}}>(GMT+09:00) Osaka, Sapporo, Tokyo</option>
                                        <option value="Asia/Seoul"  {{$timeZone?($timeZone=='Asia/Seoul'?'selected':''):''}}>(GMT+09:00) Seoul</option>
                                        <option value="Asia/Yakutsk"  {{$timeZone?($timeZone=='Asia/Yakutsk'?'selected':''):''}}>(GMT+09:00) Yakutsk</option>
                                        <option value="Australia/Adelaide"  {{$timeZone?($timeZone=='Australia/Adelaide'?'selected':''):''}}>(GMT+09:30) Adelaide</option>
                                        <option value="Australia/Darwin"  {{$timeZone?($timeZone=='Australia/Darwin'?'selected':''):''}}>(GMT+09:30) Darwin</option>
                                        <option value="Australia/Brisbane"  {{$timeZone?($timeZone=='Australia/Brisbane'?'selected':''):''}}>(GMT+10:00) Brisbane</option>
                                        <option value="Australia/Canberra"  {{$timeZone?($timeZone=='Australia/Canberra'?'selected':''):''}}>(GMT+10:00) Canberra, Melbourne, Sydney</option>
                                        <option value="Australia/Hobart"  {{$timeZone?($timeZone=='Australia/Hobart'?'selected':''):''}}>(GMT+10:00) Hobart</option>
                                        <option value="Pacific/Guam"  {{$timeZone?($timeZone=='Pacific/Guam'?'selected':''):''}}>(GMT+10:00) Guam, Port Moresby</option>
                                        <option value="Asia/Vladivostok"  {{$timeZone?($timeZone=='Asia/Vladivostok'?'selected':''):''}}>(GMT+10:00) Vladivostok</option>
                                        <option value="Asia/Magadan"  {{$timeZone?($timeZone=='Asia/Magadan'?'selected':''):''}}>(GMT+11:00) Magadan, Solomon Is., New Caledonia</option>
                                        <option value="Pacific/Auckland"  {{$timeZone?($timeZone=='Pacific/Auckland'?'selected':''):''}}>(GMT+12:00) Auckland, Wellington</option>
                                        <option value="Pacific/Fiji"  {{$timeZone?($timeZone=='Pacific/Fiji'?'selected':''):''}}>(GMT+12:00) Fiji, Kamchatka, Marshall Is.</option>
                                        <option value="Pacific/Tongatapu"  {{$timeZone?($timeZone=='Pacific/Tongatapu'?'selected':''):''}}>(GMT+13:00) Nuku'alofa</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-4">
                                @php($inactiveAuthMinute = Helpers::get_business_settings('inactive_auth_minute'))
                                <div class="form-group">
                                    <label class="input-label text-capitalize d-flex flex-wrap align-items-center column-gap-2" for="inactive_auth_minute">
                                        {{translate('Inactive auth token expire time')}}
                                        <small class="text-danger">( {{translate('In Minute')}} )</small>
                                        <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                           title="{{ translate('User will be logged out if no activity happened within this time') }}">
                                        </i>
                                    </label>
                                    <input type="number" name="inactive_auth_minute" class="form-control" id="inactive_auth_minute" value="{{$inactiveAuthMinute??''}}" min="0" required>
                                </div>
                            </div>

                            @php($phone=\App\Models\BusinessSetting::where('key','phone')->first())
                            <div class="col-sm-6 col-xl-4 ">
                                <div class="form-group">
                                    <label class="input-label">{{translate('phone')}}</label>
                                    <input type="text" value="{{$phone->value??''}}"
                                        name="phone" class="form-control"
                                        placeholder="" required>
                                </div>
                            </div>

                            @php($hotline=\App\Models\BusinessSetting::where('key','hotline_number')->first())
                            <div class="col-sm-6 col-xl-4 ">
                                <div class="form-group">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('Hotline Number')}}</label>
                                    <input type="text" value="{{$hotline->value??''}}"
                                        name="hotline_number" class="form-control"
                                        placeholder="" required>
                                </div>
                            </div>

                            @php($email=\App\Models\BusinessSetting::where('key','email')->first())
                            <div class="col-sm-6 col-xl-4 ">
                                <div class="form-group">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('email')}}</label>
                                    <input type="email" value="{{$email->value??''}}"
                                        name="email" class="form-control" placeholder=""
                                        required>
                                </div>
                            </div>

                            @php($twoFactor = \App\CentralLogics\Helpers::get_business_settings('two_factor'))
                            <div class="col-sm-6 col-xl-4">
                                <div class="form-group">
                                    <label class="input-label">{{translate('Two Factor Authentication')}}</label>
                                    <div class="input-group">
                                        <div class="form-control">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" class="custom-control-input" value="1"
                                                    name="two_factor"
                                                    id="two_factor_on" {{$twoFactor==1?'checked':''}}>
                                                <label class="custom-control-label"
                                                    for="two_factor_on">{{translate('on')}}</label>
                                            </div>
                                        </div>

                                        <div class="form-control">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" class="custom-control-input" value="0"
                                                    name="two_factor"
                                                    id="two_factor_off" {{$twoFactor==0?'checked':''}}>
                                                <label class="custom-control-label"
                                                    for="two_factor_off">{{translate('off')}}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @php($phoneVerification=\App\CentralLogics\Helpers::get_business_settings('phone_verification'))
                            <div class="col-sm-6 col-xl-4">
                                <div class="form-group">
                                    <div class="d-flex align-items-center gap-2">
                                        <label>{{translate('phone')}} {{translate('verification')}} ( OTP )</label>
                                        <small class="text-danger">*</small>
                                    </div>
                                    <div class="input-group">
                                        <div class="form-control">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" class="custom-control-input" value="1"
                                                    name="phone_verification"
                                                    id="phone_verification_on" {{ $phoneVerification==1?'checked':''}}>
                                                <label class="custom-control-label"
                                                    for="phone_verification_on">{{translate('on')}}</label>
                                            </div>
                                        </div>

                                        <div class="form-control">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" class="custom-control-input" value="0"
                                                    name="phone_verification"
                                                    id="phone_verification_off" {{ $phoneVerification==0?'checked':''}}>
                                                <label class="custom-control-label"
                                                    for="phone_verification_off">{{translate('off')}}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php($agentSelfRegistration=\App\CentralLogics\Helpers::get_business_settings('agent_self_registration'))
                            <div class="col-sm-6 col-xl-4">
                                <div class="form-group">
                                    <div class="d-flex align-items-center gap-2">
                                        <label>{{translate('Agent Self Registration')}}
                                            <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                               title="{{ translate('When this field is active agent can register themself using the agent app') }}">
                                            </i>
                                        </label>
                                    </div>
                                    <div class="input-group">
                                        <div class="form-control">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" class="custom-control-input" value="1"
                                                    name="agent_self_registration"
                                                    id="agent_self_registration_on" {{ $agentSelfRegistration==1?'checked':''}}>
                                                <label class="custom-control-label"
                                                    for="agent_self_registration_on">{{translate('on')}}</label>
                                            </div>
                                        </div>

                                        <div class="form-control">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" class="custom-control-input" value="0"
                                                    name="agent_self_registration"
                                                    id="agent_self_registration_off" {{ $agentSelfRegistration==0?'checked':''}}>
                                                <label class="custom-control-label"
                                                    for="agent_self_registration_off">{{translate('off')}}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-4">
                                @php($address=\App\Models\BusinessSetting::where('key','address')->first())
                                <div class="form-group">
                                    <label class="input-label">{{translate('address')}}</label>
                                    <textarea type="text" id="address" name="address" class="form-control"
                                            rows="1" required>{{$address->value??''}}</textarea>
                                </div>
                            </div>

                            @php($footerText=\App\Models\BusinessSetting::where('key','footer_text')->first())
                            <div class="col-sm-6 col-xl-4">
                                <div class="form-group">
                                    <label class="input-label">{{translate('footer')}} {{translate('text')}}</label>
                                    <textarea type="text" name="footer_text" class="form-control" rows="1" required>{{$footerText->value??''}}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            @php($agentSelfDelete=\App\CentralLogics\Helpers::get_business_settings('agent_self_delete'))
                            @php($customerSelfDelete=\App\CentralLogics\Helpers::get_business_settings('customer_self_delete'))
                            <div class="col-sm-6 col-xl-4">
                                <div class="form-group">
                                    <div class="d-flex align-items-center gap-2">
                                        <label>{{translate('Customer Self Delete')}}
                                            <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                               title="{{ translate('When this field is active customer can delete account') }}">

                                            </i>
                                        </label>
                                    </div>
                                    <div class="input-group">
                                        <div class="form-control">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" class="custom-control-input" value="1"
                                                    name="customer_self_delete"
                                                    id="customer_self_delete_on" {{ $customerSelfDelete==1?'checked':''}}>
                                                <label class="custom-control-label"
                                                    for="customer_self_delete_on">{{translate('on')}}</label>
                                            </div>
                                        </div>

                                        <div class="form-control">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" class="custom-control-input" value="0"
                                                    name="customer_self_delete"
                                                    id="customer_self_delete_off" {{ $customerSelfDelete==0?'checked':''}}>
                                                <label class="custom-control-label"
                                                    for="customer_self_delete_off">{{translate('off')}}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-xl-4">
                                <div class="form-group">
                                    <div class="d-flex align-items-center gap-2">
                                        <label>{{translate('Agent Self Delete')}}
                                            <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                               title="{{ translate('When this field is active agent can delete account') }}">

                                            </i>
                                        </label>
                                    </div>
                                    <div class="input-group">
                                        <div class="form-control">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" class="custom-control-input" value="1"
                                                    name="agent_self_delete"
                                                    id="agent_self_delete_on" {{ $agentSelfDelete==1?'checked':''}}>
                                                <label class="custom-control-label"
                                                    for="agent_self_delete_on">{{translate('on')}}</label>
                                            </div>
                                        </div>

                                        <div class="form-control">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" class="custom-control-input" value="0"
                                                    name="agent_self_delete"
                                                    id="agent_self_delete_off" {{ $agentSelfDelete==0?'checked':''}}>
                                                <label class="custom-control-label"
                                                    for="agent_self_delete_off">{{translate('off')}}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @php($businessShortDescription=\App\Models\BusinessSetting::where('key','business_short_description')->first())
                            <div class="col-sm-6 col-xl-4 ">
                                <div class="form-group">
                                    <label class="input-label" for="business_short_description">{{translate('business_short_description')}}</label>
                                    <input type="text" value="{{$businessShortDescription->value??''}}"
                                        name="business_short_description" class="form-control" placeholder="">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="input-label d-flex align-items-center gap-2">{{translate('logo')}}
                                        <small class="text-danger">* ( {{translate('ratio')}} 3:1 )</small>
                                    </label>

                                    <div class="custom-file">
                                        <input type="file" name="logo" id="customFileEg1" class="custom-file-input"
                                               accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="custom-file-label" for="customFileEg1">{{translate('choose File')}}</label>
                                    </div>

                                    <div class="text-center mt-3">
                                        <img class="border rounded-10 mx-w300 w-100" id="viewer"
                                             src="{{$logo}}" alt="{{ translate('logo') }}"/>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="input-label d-flex align-items-center gap-2">{{translate('Favicon')}}
                                        <small class="text-danger">* ( {{translate('ratio')}} 1:1 )</small>
                                    </label>

                                    <div class="custom-file">
                                        <input type="file" name="favicon" id="customFileEg2" class="custom-file-input"
                                               accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="custom-file-label" for="customFileEg2">{{translate('choose File')}}</label>
                                    </div>

                                    <div class="text-center mt-3 overflow-hidden aspect-ratio-2">
                                        <img class="border rounded-10 w-100 img-fit max-height-180px max-width-180px" id="viewer1"
                                             src="{{$favicon}}" alt="{{ translate('favicon') }}"/>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label class="input-label d-flex align-items-center gap-2">{{translate('landing_page_logo')}}
                                        <small class="text-danger">* ( {{translate('ratio')}} 3:1 )</small>
                                    </label>

                                    <div class="custom-file">
                                        <input type="file" name="landing_page_logo" id="customFileEg3" class="custom-file-input"
                                               accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="custom-file-label" for="customFileEg3">{{translate('choose File')}}</label>
                                    </div>

                                    <div class="text-center mt-3">
                                        <img class="border rounded-10 mx-w300 w-100" id="viewer3"
                                             src="{{$landingPageLogo}}" alt="{{ translate('landing_page_logo') }}"/>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}" class="btn btn-primary demo-form-submit">{{translate('submit')}}</button>
                        </div>
                    </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        "use strict";

        function readURL(input, viewId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $(`#${viewId}`).attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function () {
            readURL(this, 'viewer');
        });
        $("#customFileEg2").change(function () {
            readURL(this, 'viewer1');
        });
        $("#customFileEg3").change(function () {
            readURL(this, 'viewer3');
        });
    </script>

    <script>
        $(document).on('ready', function () {
            @php($country=\App\CentralLogics\Helpers::get_business_settings('country')??'BD')
            $("#country option[value='{{$country}}']").attr('selected', 'selected').change();
        })
    </script>
@endpush
