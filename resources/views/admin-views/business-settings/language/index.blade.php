@extends('layouts.admin.app')

@section('title', translate('Language'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 pb-2">
            <img width="24" src="{{asset('public/assets/admin/img/media/business-setup.png')}}" alt="{{translate('business_setup')}}">
            <h2 class="page-header-title">{{translate('Business Setup')}}</h2>
        </div>

        <div class="inline-page-menu my-4">
            @include('admin-views.business-settings.partial._business-setup-tabs')
        </div>

        <div class="alert alert-danger mb-3" role="alert">
            {{translate('changing_some_settings_will_take_time_to_show_effect_please_clear_session_or_wait_for_60_minutes_else_browse_from_incognito_mode')}}
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{translate('language_table')}}</h5>
                <button class="btn btn-primary" data-toggle="modal"
                        data-target="#lang-modal">
                    <i class="tio-add"></i>
                    <span class="text">{{translate('add_new_language')}}</span>
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ translate('SL')}}</th>
                            <th>{{translate('name')}}</th>
                            <th>{{translate('Code')}}</th>
                            <th class="text-center">{{translate('status')}}</th>
                            <th class="text-center">{{translate('default')}} {{translate('status')}}</th>
                            <th class="text-center">{{translate('action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @php($language = App\CentralLogics\Helpers::get_business_settings('language'))
                    @if(isset($language))
                        @foreach($language as $key =>$data)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$data['name']}}
                                </td>
                                <td>{{$data['code']}}</td>
                                <td class="text-center">
                                    <label class="switcher mx-auto">
                                        <input type="checkbox"
                                               data-route="{{route('admin.business-settings.language.update-status')}}"
                                               data-code="{{$data['code']}}"
                                                class="switcher_input language-status-change" {{$data['status']==1?'checked':''}}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </td>
                                <td class="text-center">
                                    <label class="switcher mx-auto">
                                        <input type="checkbox"
                                               data-route="{{route('admin.business-settings.language.update-default-status', ['code'=>$data['code']])}}"
                                                class="switcher_input change-status" {{ ((array_key_exists('default', $data) && $data['default']==true) ? 'checked': ((array_key_exists('default', $data) && $data['default']==false) ? '' : 'disabled')) }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        @if($data['code']!='en')
                                            <a href="#" class="action-btn btn btn-outline-primary" data-toggle="modal"
                                                data-target="#lang-modal-update-{{$data['code']}}">
                                                <i class="tio-edit" aria-hidden="true"></i>
                                            </a>
                                            @if($data['default'] != true)
                                                <button class="action-btn btn btn-outline-danger delete-language" id="delete"
                                                        data-route="{{ route('admin.business-settings.language.delete',[$data['code']]) }}">
                                                    <i class="tio-add-to-trash" aria-hidden="true"></i>
                                                </button>
                                            @endif
                                        @endif
                                        <a class="action-btn btn btn-outline-info"
                                            href="{{route('admin.business-settings.language.translate',[$data['code']])}}">
                                            <i class="tio-book-outlined"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade" id="lang-modal" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header border-bottom pb-3">
                        <h5 class="mb-0" id="exampleModalLabel">{{translate('new_language')}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{route('admin.business-settings.language.add-new')}}" method="post"
                          style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="recipient-name">{{translate('language')}} </label>
                                <input type="text" class="form-control" id="recipient-name" name="name" required>
                            </div>
                            <div class="">
                                <label for="message-text">{{translate('country_code')}}</label>
                                <select class="form-control js-select2-custom w-100" name="code">
                                    <option value="af">Afrikaans</option>
                                    <option value="sq">Albanian - shqip</option>
                                    <option value="am">Amharic - አማርኛ</option>
                                    <option value="ar">Arabic - العربية</option>
                                    <option value="an">Aragonese - aragonés</option>
                                    <option value="hy">Armenian - հայերեն</option>
                                    <option value="ast">Asturian - asturianu</option>
                                    <option value="az">Azerbaijani - azərbaycan dili</option>
                                    <option value="eu">Basque - euskara</option>
                                    <option value="be">Belarusian - беларуская</option>
                                    <option value="bn">Bengali - বাংলা</option>
                                    <option value="bs">Bosnian - bosanski</option>
                                    <option value="br">Breton - brezhoneg</option>
                                    <option value="bg">Bulgarian - български</option>
                                    <option value="ca">Catalan - català</option>
                                    <option value="ckb">Central Kurdish - کوردی (دەستنوسی عەرەبی)</option>
                                    <option value="zh">Chinese - 中文</option>
                                    <option value="zh-HK">Chinese (Hong Kong) - 中文（香港）</option>
                                    <option value="zh-CN">Chinese (Simplified) - 中文（简体）</option>
                                    <option value="zh-TW">Chinese (Traditional) - 中文（繁體）</option>
                                    <option value="co">Corsican</option>
                                    <option value="hr">Croatian - hrvatski</option>
                                    <option value="cs">Czech - čeština</option>
                                    <option value="da">Danish - dansk</option>
                                    <option value="nl">Dutch - Nederlands</option>
                                    <option value="en-AU">English (Australia)</option>
                                    <option value="en-CA">English (Canada)</option>
                                    <option value="en-IN">English (India)</option>
                                    <option value="en-NZ">English (New Zealand)</option>
                                    <option value="en-ZA">English (South Africa)</option>
                                    <option value="en-GB">English (United Kingdom)</option>
                                    <option value="en-US">English (United States)</option>
                                    <option value="eo">Esperanto - esperanto</option>
                                    <option value="et">Estonian - eesti</option>
                                    <option value="fo">Faroese - føroyskt</option>
                                    <option value="fil">Filipino</option>
                                    <option value="fi">Finnish - suomi</option>
                                    <option value="fr">French - français</option>
                                    <option value="fr-CA">French (Canada) - français (Canada)</option>
                                    <option value="fr-FR">French (France) - français (France)</option>
                                    <option value="fr-CH">French (Switzerland) - français (Suisse)</option>
                                    <option value="gl">Galician - galego</option>
                                    <option value="ka">Georgian - ქართული</option>
                                    <option value="de">German - Deutsch</option>
                                    <option value="de-AT">German (Austria) - Deutsch (Österreich)</option>
                                    <option value="de-DE">German (Germany) - Deutsch (Deutschland)</option>
                                    <option value="de-LI">German (Liechtenstein) - Deutsch (Liechtenstein)</option>
                                    <option value="de-CH">German (Switzerland) - Deutsch (Schweiz)</option>
                                    <option value="el">Greek - Ελληνικά</option>
                                    <option value="gn">Guarani</option>
                                    <option value="gu">Gujarati - ગુજરાતી</option>
                                    <option value="ha">Hausa</option>
                                    <option value="haw">Hawaiian - ʻŌlelo Hawaiʻi</option>
                                    <option value="he">Hebrew - עברית</option>
                                    <option value="hi">Hindi - हिन्दी</option>
                                    <option value="hu">Hungarian - magyar</option>
                                    <option value="is">Icelandic - íslenska</option>
                                    <option value="id">Indonesian - Indonesia</option>
                                    <option value="ia">Interlingua</option>
                                    <option value="ga">Irish - Gaeilge</option>
                                    <option value="it">Italian - italiano</option>
                                    <option value="it-IT">Italian (Italy) - italiano (Italia)</option>
                                    <option value="it-CH">Italian (Switzerland) - italiano (Svizzera)</option>
                                    <option value="ja">Japanese - 日本語</option>
                                    <option value="kn">Kannada - ಕನ್ನಡ</option>
                                    <option value="kk">Kazakh - қазақ тілі</option>
                                    <option value="km">Khmer - ខ្មែរ</option>
                                    <option value="ko">Korean - 한국어</option>
                                    <option value="ku">Kurdish - Kurdî</option>
                                    <option value="ky">Kyrgyz - кыргызча</option>
                                    <option value="lo">Lao - ລາວ</option>
                                    <option value="la">Latin</option>
                                    <option value="lv">Latvian - latviešu</option>
                                    <option value="ln">Lingala - lingála</option>
                                    <option value="lt">Lithuanian - lietuvių</option>
                                    <option value="mk">Macedonian - македонски</option>
                                    <option value="ms">Malay - Bahasa Melayu</option>
                                    <option value="ml">Malayalam - മലയാളം</option>
                                    <option value="mt">Maltese - Malti</option>
                                    <option value="mr">Marathi - मराठी</option>
                                    <option value="mn">Mongolian - монгол</option>
                                    <option value="ne">Nepali - नेपाली</option>
                                    <option value="no">Norwegian - norsk</option>
                                    <option value="nb">Norwegian Bokmål - norsk bokmål</option>
                                    <option value="nn">Norwegian Nynorsk - nynorsk</option>
                                    <option value="oc">Occitan</option>
                                    <option value="or">Oriya - ଓଡ଼ିଆ</option>
                                    <option value="om">Oromo - Oromoo</option>
                                    <option value="ps">Pashto - پښتو</option>
                                    <option value="fa">Persian - فارسی</option>
                                    <option value="pl">Polish - polski</option>
                                    <option value="pt">Portuguese - português</option>
                                    <option value="pt-BR">Portuguese (Brazil) - português (Brasil)</option>
                                    <option value="pt-PT">Portuguese (Portugal) - português (Portugal)</option>
                                    <option value="pa">Punjabi - ਪੰਜਾਬੀ</option>
                                    <option value="qu">Quechua</option>
                                    <option value="ro">Romanian - română</option>
                                    <option value="mo">Romanian (Moldova) - română (Moldova)</option>
                                    <option value="rm">Romansh - rumantsch</option>
                                    <option value="ru">Russian - русский</option>
                                    <option value="gd">Scottish Gaelic</option>
                                    <option value="sr">Serbian - српски</option>
                                    <option value="sh">Serbo-Croatian - Srpskohrvatski</option>
                                    <option value="sn">Shona - chiShona</option>
                                    <option value="sd">Sindhi</option>
                                    <option value="si">Sinhala - සිංහල</option>
                                    <option value="sk">Slovak - slovenčina</option>
                                    <option value="sl">Slovenian - slovenščina</option>
                                    <option value="so">Somali - Soomaali</option>
                                    <option value="st">Southern Sotho</option>
                                    <option value="es">Spanish - español</option>
                                    <option value="es-AR">Spanish (Argentina) - español (Argentina)</option>
                                    <option value="es-419">Spanish (Latin America) - español (Latinoamérica)</option>
                                    <option value="es-MX">Spanish (Mexico) - español (México)</option>
                                    <option value="es-ES">Spanish (Spain) - español (España)</option>
                                    <option value="es-US">Spanish (United States) - español (Estados Unidos)</option>
                                    <option value="su">Sundanese</option>
                                    <option value="sw">Swahili - Kiswahili</option>
                                    <option value="sv">Swedish - svenska</option>
                                    <option value="tg">Tajik - тоҷикӣ</option>
                                    <option value="ta">Tamil - தமிழ்</option>
                                    <option value="tt">Tatar</option>
                                    <option value="te">Telugu - తెలుగు</option>
                                    <option value="th">Thai - ไทย</option>
                                    <option value="ti">Tigrinya - ትግርኛ</option>
                                    <option value="to">Tongan - lea fakatonga</option>
                                    <option value="tr">Turkish - Türkçe</option>
                                    <option value="tk">Turkmen</option>
                                    <option value="tw">Twi</option>
                                    <option value="uk">Ukrainian - українська</option>
                                    <option value="ur">Urdu - اردو</option>
                                    <option value="ug">Uyghur</option>
                                    <option value="uz">Uzbek - o‘zbek</option>
                                    <option value="vi">Vietnamese - Tiếng Việt</option>
                                    <option value="wa">Walloon - wa</option>
                                    <option value="cy">Welsh - Cymraeg</option>
                                    <option value="fy">Western Frisian</option>
                                    <option value="xh">Xhosa</option>
                                    <option value="yi">Yiddish</option>
                                    <option value="yo">Yoruba - Èdè Yorùbá</option>
                                    <option value="zu">Zulu - isiZulu</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('close')}}</button>
                            <button type="submit" class="btn btn-primary">{{translate('Add')}} <i class="fa fa-plus"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if(isset($language))
            @foreach($language as $key =>$data)
                <div class="modal fade" id="lang-modal-update-{{$data['code']}}" tabindex="-1" role="dialog"
                     aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header border-bottom pb-3">
                                <h5 class="mb-0"
                                    id="exampleModalLabel">{{translate('update_language')}}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form action="{{route('admin.business-settings.language.update')}}" method="post">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="recipient-name">{{translate('language')}} </label>
                                        <input type="text" class="form-control" value="{{$data['name']}}" name="name" required>
                                    </div>
                                    <div class="">
                                        <label for="message-text">{{translate('country_code')}}</label>
                                        <span></span>
                                        <select class="form-control w-100" name="code">
                                            <option value="af" {{ $data['code'] == 'af' ? 'selected' : 'style=display:none' }}>Afrikaans</option>
                                            <option value="sq" {{ $data['code'] == 'sq' ? 'selected' : 'style=display:none' }}>Albanian - shqip</option>
                                            <option value="am" {{ $data['code'] == 'am' ? 'selected' : 'style=display:none' }}>Amharic - አማርኛ</option>
                                            <option value="ar" {{ $data['code'] == 'ar' ? 'selected' : 'style=display:none' }}>Arabic - العربية</option>
                                            <option value="an" {{ $data['code'] == 'an' ? 'selected' : 'style=display:none' }}>Aragonese - aragonés</option>
                                            <option value="hy" {{ $data['code'] == 'hy' ? 'selected' : 'style=display:none' }}>Armenian - հայերեն</option>
                                            <option value="ast" {{ $data['code'] == 'ast' ? 'selected' : 'style=display:none' }}>Asturian - asturianu</option>
                                            <option value="az" {{ $data['code'] == 'az' ? 'selected' : 'style=display:none' }}>Azerbaijani - azərbaycan dili</option>
                                            <option value="eu" {{ $data['code'] == 'eu' ? 'selected' : 'style=display:none' }}>Basque - euskara</option>
                                            <option value="be" {{ $data['code'] == 'be' ? 'selected' : 'style=display:none' }}>Belarusian - беларуская</option>
                                            <option value="bn" {{ $data['code'] == 'bn' ? 'selected' : 'style=display:none' }}>Bengali - বাংলা</option>
                                            <option value="bs" {{ $data['code'] == 'bs' ? 'selected' : 'style=display:none' }}>Bosnian - bosanski</option>
                                            <option value="br" {{ $data['code'] == 'br' ? 'selected' : 'style=display:none' }}>Breton - brezhoneg</option>
                                            <option value="bg" {{ $data['code'] == 'bg' ? 'selected' : 'style=display:none' }}>Bulgarian - български</option>
                                            <option value="ca" {{ $data['code'] == 'ca' ? 'selected' : 'style=display:none' }}>Catalan - català</option>
                                            <option value="ckb" {{ $data['code'] == 'ckb' ? 'selected' : 'style=display:none' }}>Central Kurdish - کوردی (دەستنوسی عەرەبی)</option>
                                            <option value="zh" {{ $data['code'] == 'zh' ? 'selected' : 'style=display:none' }}>Chinese - 中文</option>
                                            <option value="zh-HK" {{ $data['code'] == 'zh-HK' ? 'selected' : 'style=display:none' }}>Chinese (Hong Kong) - 中文（香港）</option>
                                            <option value="zh-CN" {{ $data['code'] == 'zh-CN' ? 'selected' : 'style=display:none' }}>Chinese (Simplified) - 中文（简体）</option>
                                            <option value="zh-TW" {{ $data['code'] == 'zh-TW' ? 'selected' : 'style=display:none' }}>Chinese (Traditional) - 中文（繁體）</option>
                                            <option value="co" {{ $data['code'] == 'co' ? 'selected' : 'style=display:none' }}>Corsican</option>
                                            <option value="hr" {{ $data['code'] == 'hr' ? 'selected' : 'style=display:none' }}>Croatian - hrvatski</option>
                                            <option value="cs" {{ $data['code'] == 'cs' ? 'selected' : 'style=display:none' }}>Czech - čeština</option>
                                            <option value="da" {{ $data['code'] == 'da' ? 'selected' : 'style=display:none' }}>Danish - dansk</option>
                                            <option value="nl" {{ $data['code'] == 'nl' ? 'selected' : 'style=display:none' }}>Dutch - Nederlands</option>
                                            <option value="en-AU" {{ $data['code'] == 'en-AU' ? 'selected' : 'style=display:none' }}>English (Australia)</option>
                                            <option value="en-CA" {{ $data['code'] == 'en-CA' ? 'selected' : 'style=display:none' }}>English (Canada)</option>
                                            <option value="en-IN" {{ $data['code'] == 'en-IN' ? 'selected' : 'style=display:none' }}>English (India)</option>
                                            <option value="en-NZ" {{ $data['code'] == 'en-NZ' ? 'selected' : 'style=display:none' }}>English (New Zealand)</option>
                                            <option value="en-ZA" {{ $data['code'] == 'en-ZA' ? 'selected' : 'style=display:none' }}>English (South Africa)</option>
                                            <option value="en-GB" {{ $data['code'] == 'en-GB' ? 'selected' : 'style=display:none' }}>English (United Kingdom)</option>
                                            <option value="en-US" {{ $data['code'] == 'en-US' ? 'selected' : 'style=display:none' }}>English (United States)</option>
                                            <option value="eo" {{ $data['code'] == 'eo' ? 'selected' : 'style=display:none' }}>Esperanto - esperanto</option>
                                            <option value="et" {{ $data['code'] == 'et' ? 'selected' : 'style=display:none' }}>Estonian - eesti</option>
                                            <option value="fo" {{ $data['code'] == 'fo' ? 'selected' : 'style=display:none' }}>Faroese - føroyskt</option>
                                            <option value="fil" {{ $data['code'] == 'fil' ? 'selected' : 'style=display:none' }}>Filipino</option>
                                            <option value="fi" {{ $data['code'] == 'fi' ? 'selected' : 'style=display:none' }}>Finnish - suomi</option>
                                            <option value="fr" {{ $data['code'] == 'fr' ? 'selected' : 'style=display:none' }}>French - français</option>
                                            <option value="fr-CA" {{ $data['code'] == 'fr-CA' ? 'selected' : 'style=display:none' }}>French (Canada) - français (Canada)</option>
                                            <option value="fr-FR" {{ $data['code'] == 'fr-FR' ? 'selected' : 'style=display:none' }}>French (France) - français (France)</option>
                                            <option value="fr-CH" {{ $data['code'] == 'fr-CH' ? 'selected' : 'style=display:none' }}>French (Switzerland) - français (Suisse)</option>
                                            <option value="gl" {{ $data['code'] == 'gl' ? 'selected' : 'style=display:none' }}>Galician - galego</option>
                                            <option value="ka" {{ $data['code'] == 'ka' ? 'selected' : 'style=display:none' }}>Georgian - ქართული</option>
                                            <option value="de" {{ $data['code'] == 'de' ? 'selected' : 'style=display:none' }}>German - Deutsch</option>
                                            <option value="de-AT" {{ $data['code'] == 'de-AT' ? 'selected' : 'style=display:none' }}>German (Austria) - Deutsch (Österreich)</option>
                                            <option value="de-DE" {{ $data['code'] == 'de-DE' ? 'selected' : 'style=display:none' }}>German (Germany) - Deutsch (Deutschland)</option>
                                            <option value="de-LI" {{ $data['code'] == 'de-LI' ? 'selected' : 'style=display:none' }}>German (Liechtenstein) - Deutsch (Liechtenstein)</option>
                                            <option value="de-CH" {{ $data['code'] == 'de-CH' ? 'selected' : 'style=display:none' }}>German (Switzerland) - Deutsch (Schweiz)</option>
                                            <option value="el" {{ $data['code'] == 'el' ? 'selected' : 'style=display:none' }}>Greek - Ελληνικά</option>
                                            <option value="gn" {{ $data['code'] == 'gn' ? 'selected' : 'style=display:none' }}>Guarani</option>
                                            <option value="gu" {{ $data['code'] == 'gu' ? 'selected' : 'style=display:none' }}>Gujarati - ગુજરાતી</option>
                                            <option value="ha" {{ $data['code'] == 'ha' ? 'selected' : 'style=display:none' }}>Hausa</option>
                                            <option value="haw" {{ $data['code'] == 'haw' ? 'selected' : 'style=display:none' }}>Hawaiian - ʻŌlelo Hawaiʻi</option>
                                            <option value="he" {{ $data['code'] == 'he' ? 'selected' : 'style=display:none' }}>Hebrew - עברית</option>
                                            <option value="hi" {{ $data['code'] == 'hi' ? 'selected' : 'style=display:none' }}>Hindi - हिन्दी</option>
                                            <option value="hu" {{ $data['code'] == 'hu' ? 'selected' : 'style=display:none' }}>Hungarian - magyar</option>
                                            <option value="is" {{ $data['code'] == 'is' ? 'selected' : 'style=display:none' }}>Icelandic - íslenska</option>
                                            <option value="id" {{ $data['code'] == 'id' ? 'selected' : 'style=display:none' }}>Indonesian - Indonesia</option>
                                            <option value="ia" {{ $data['code'] == 'ia' ? 'selected' : 'style=display:none' }}>Interlingua</option>
                                            <option value="ga" {{ $data['code'] == 'ga' ? 'selected' : 'style=display:none' }}>Irish - Gaeilge</option>
                                            <option value="it" {{ $data['code'] == 'it' ? 'selected' : 'style=display:none' }}>Italian - italiano</option>
                                            <option value="it-IT" {{ $data['code'] == 'it-IT' ? 'selected' : 'style=display:none' }}>Italian (Italy) - italiano (Italia)</option>
                                            <option value="it-CH" {{ $data['code'] == 'it-CH' ? 'selected' : 'style=display:none' }}>Italian (Switzerland) - italiano (Svizzera)</option>
                                            <option value="ja" {{ $data['code'] == 'ja' ? 'selected' : 'style=display:none' }}>Japanese - 日本語</option>
                                            <option value="kn" {{ $data['code'] == 'kn' ? 'selected' : 'style=display:none' }}>Kannada - ಕನ್ನಡ</option>
                                            <option value="kk" {{ $data['code'] == 'kk' ? 'selected' : 'style=display:none' }}>Kazakh - қазақ тілі</option>
                                            <option value="km" {{ $data['code'] == 'km' ? 'selected' : 'style=display:none' }}>Khmer - ខ្មែរ</option>
                                            <option value="ko" {{ $data['code'] == 'ko' ? 'selected' : 'style=display:none' }}>Korean - 한국어</option>
                                            <option value="ku" {{ $data['code'] == 'ku' ? 'selected' : 'style=display:none' }}>Kurdish - Kurdî</option>
                                            <option value="ky" {{ $data['code'] == 'ky' ? 'selected' : 'style=display:none' }}>Kyrgyz - кыргызча</option>
                                            <option value="lo" {{ $data['code'] == 'lo' ? 'selected' : 'style=display:none' }}>Lao - ລາວ</option>
                                            <option value="la" {{ $data['code'] == 'la' ? 'selected' : 'style=display:none' }}>Latin</option>
                                            <option value="lv" {{ $data['code'] == 'lv' ? 'selected' : 'style=display:none' }}>Latvian - latviešu</option>
                                            <option value="ln" {{ $data['code'] == 'ln' ? 'selected' : 'style=display:none' }}>Lingala - lingála</option>
                                            <option value="lt" {{ $data['code'] == 'lt' ? 'selected' : 'style=display:none' }}>Lithuanian - lietuvių</option>
                                            <option value="mk" {{ $data['code'] == 'mk' ? 'selected' : 'style=display:none' }}>Macedonian - македонски</option>
                                            <option value="ms" {{ $data['code'] == 'ms' ? 'selected' : 'style=display:none' }}>Malay - Bahasa Melayu</option>
                                            <option value="ml" {{ $data['code'] == 'ml' ? 'selected' : 'style=display:none' }}>Malayalam - മലയാളം</option>
                                            <option value="mt" {{ $data['code'] == 'mt' ? 'selected' : 'style=display:none' }}>Maltese - Malti</option>
                                            <option value="mr" {{ $data['code'] == 'mr' ? 'selected' : 'style=display:none' }}>Marathi - मराठी</option>
                                            <option value="mn" {{ $data['code'] == 'mn' ? 'selected' : 'style=display:none' }}>Mongolian - монгол</option>
                                            <option value="ne" {{ $data['code'] == 'ne' ? 'selected' : 'style=display:none' }}>Nepali - नेपाली</option>
                                            <option value="no" {{ $data['code'] == 'no' ? 'selected' : 'style=display:none' }}>Norwegian - norsk</option>
                                            <option value="nb" {{ $data['code'] == 'nb' ? 'selected' : 'style=display:none' }}>Norwegian Bokmål - norsk bokmål</option>
                                            <option value="nn" {{ $data['code'] == 'nn' ? 'selected' : 'style=display:none' }}>Norwegian Nynorsk - nynorsk</option>
                                            <option value="oc" {{ $data['code'] == 'oc' ? 'selected' : 'style=display:none' }}>Occitan</option>
                                            <option value="or" {{ $data['code'] == 'or' ? 'selected' : 'style=display:none' }}>Oriya - ଓଡ଼ିଆ</option>
                                            <option value="om" {{ $data['code'] == 'om' ? 'selected' : 'style=display:none' }}>Oromo - Oromoo</option>
                                            <option value="ps" {{ $data['code'] == 'ps' ? 'selected' : 'style=display:none' }}>Pashto - پښتو</option>
                                            <option value="fa" {{ $data['code'] == 'fa' ? 'selected' : 'style=display:none' }}>Persian - فارسی</option>
                                            <option value="pl" {{ $data['code'] == 'pl' ? 'selected' : 'style=display:none' }}>Polish - polski</option>
                                            <option value="pt" {{ $data['code'] == 'pt' ? 'selected' : 'style=display:none' }}>Portuguese - português</option>
                                            <option value="pt-BR" {{ $data['code'] == 'pt-BR' ? 'selected' : 'style=display:none' }}>Portuguese (Brazil) - português (Brasil)</option>
                                            <option value="pt-PT" {{ $data['code'] == 'pt-PT' ? 'selected' : 'style=display:none' }}>Portuguese (Portugal) - português (Portugal)</option>
                                            <option value="pa" {{ $data['code'] == 'pa' ? 'selected' : 'style=display:none' }}>Punjabi - ਪੰਜਾਬੀ</option>
                                            <option value="qu" {{ $data['code'] == 'qu' ? 'selected' : 'style=display:none' }}>Quechua</option>
                                            <option value="ro" {{ $data['code'] == 'ro' ? 'selected' : 'style=display:none' }}>Romanian - română</option>
                                            <option value="mo" {{ $data['code'] == 'mo' ? 'selected' : 'style=display:none' }}>Romanian (Moldova) - română (Moldova)</option>
                                            <option value="rm" {{ $data['code'] == 'rm' ? 'selected' : 'style=display:none' }}>Romansh - rumantsch</option>
                                            <option value="ru" {{ $data['code'] == 'ru' ? 'selected' : 'style=display:none' }}>Russian - русский</option>
                                            <option value="gd" {{ $data['code'] == 'gd' ? 'selected' : 'style=display:none' }}>Scottish Gaelic</option>
                                            <option value="sr" {{ $data['code'] == 'sr' ? 'selected' : 'style=display:none' }}>Serbian - српски</option>
                                            <option value="sh" {{ $data['code'] == 'sh' ? 'selected' : 'style=display:none' }}>Serbo-Croatian - Srpskohrvatski</option>
                                            <option value="sn" {{ $data['code'] == 'sn' ? 'selected' : 'style=display:none' }}>Shona - chiShona</option>
                                            <option value="sd" {{ $data['code'] == 'sd' ? 'selected' : 'style=display:none' }}>Sindhi</option>
                                            <option value="si" {{ $data['code'] == 'si' ? 'selected' : 'style=display:none' }}>Sinhala - සිංහල</option>
                                            <option value="sk" {{ $data['code'] == 'sk' ? 'selected' : 'style=display:none' }}>Slovak - slovenčina</option>
                                            <option value="sl" {{ $data['code'] == 'sl' ? 'selected' : 'style=display:none' }}>Slovenian - slovenščina</option>
                                            <option value="so" {{ $data['code'] == 'so' ? 'selected' : 'style=display:none' }}>Somali - Soomaali</option>
                                            <option value="st" {{ $data['code'] == 'st' ? 'selected' : 'style=display:none' }}>Southern Sotho</option>
                                            <option value="es" {{ $data['code'] == 'es' ? 'selected' : 'style=display:none' }}>Spanish - español</option>
                                            <option value="es-AR" {{ $data['code'] == 'es-AR' ? 'selected' : 'style=display:none' }}>Spanish (Argentina) - español (Argentina)</option>
                                            <option value="es-419" {{ $data['code'] == 'es-419' ? 'selected' : 'style=display:none' }}>Spanish (Latin America) - español (Latinoamérica)</option>
                                            <option value="es-MX" {{ $data['code'] == 'es-MX' ? 'selected' : 'style=display:none' }}>Spanish (Mexico) - español (México)</option>
                                            <option value="es-ES" {{ $data['code'] == 'es-ES' ? 'selected' : 'style=display:none' }}>Spanish (Spain) - español (España)</option>
                                            <option value="es-US" {{ $data['code'] == 'es-US' ? 'selected' : 'style=display:none' }}>Spanish (United States) - español (Estados Unidos)</option>
                                            <option value="su" {{ $data['code'] == 'su' ? 'selected' : 'style=display:none' }}>Sundanese</option>
                                            <option value="sw" {{ $data['code'] == 'sw' ? 'selected' : 'style=display:none' }}>Swahili - Kiswahili</option>
                                            <option value="sv" {{ $data['code'] == 'sv' ? 'selected' : 'style=display:none' }}>Swedish - svenska</option>
                                            <option value="tg" {{ $data['code'] == 'tg' ? 'selected' : 'style=display:none' }}>Tajik - тоҷикӣ</option>
                                            <option value="ta" {{ $data['code'] == 'ta' ? 'selected' : 'style=display:none' }}>Tamil - தமிழ்</option>
                                            <option value="tt" {{ $data['code'] == 'tt' ? 'selected' : 'style=display:none' }}>Tatar</option>
                                            <option value="te" {{ $data['code'] == 'te' ? 'selected' : 'style=display:none' }}>Telugu - తెలుగు</option>
                                            <option value="th" {{ $data['code'] == 'th' ? 'selected' : 'style=display:none' }}>Thai - ไทย</option>
                                            <option value="ti" {{ $data['code'] == 'ti' ? 'selected' : 'style=display:none' }}>Tigrinya - ትግርኛ</option>
                                            <option value="to" {{ $data['code'] == 'to' ? 'selected' : 'style=display:none' }}>Tongan - lea fakatonga</option>
                                            <option value="tr" {{ $data['code'] == 'tr' ? 'selected' : 'style=display:none' }}>Turkish - Türkçe</option>
                                            <option value="tk" {{ $data['code'] == 'tk' ? 'selected' : 'style=display:none' }}>Turkmen</option>
                                            <option value="tw" {{ $data['code'] == 'tw' ? 'selected' : 'style=display:none' }}>Twi</option>
                                            <option value="uk" {{ $data['code'] == 'uk' ? 'selected' : 'style=display:none' }}>Ukrainian - українська</option>
                                            <option value="ur" {{ $data['code'] == 'ur' ? 'selected' : 'style=display:none' }}>Urdu - اردو</option>
                                            <option value="ug" {{ $data['code'] == 'ug' ? 'selected' : 'style=display:none' }}>Uyghur</option>
                                            <option value="uz" {{ $data['code'] == 'uz' ? 'selected' : 'style=display:none' }}>Uzbek - o‘zbek</option>
                                            <option value="vi" {{ $data['code'] == 'vi' ? 'selected' : 'style=display:none' }}>Vietnamese - Tiếng Việt</option>
                                            <option value="wa" {{ $data['code'] == 'wa' ? 'selected' : 'style=display:none' }}>Walloon - wa</option>
                                            <option value="cy" {{ $data['code'] == 'cy' ? 'selected' : 'style=display:none' }}>Welsh - Cymraeg</option>
                                            <option value="fy" {{ $data['code'] == 'fy' ? 'selected' : 'style=display:none' }}>Western Frisian</option>
                                            <option value="xh" {{ $data['code'] == 'xh' ? 'selected' : 'style=display:none' }}>Xhosa</option>
                                            <option value="yi" {{ $data['code'] == 'yi' ? 'selected' : 'style=display:none' }}>Yiddish</option>
                                            <option value="yo" {{ $data['code'] == 'yo' ? 'selected' : 'style=display:none' }}>Yoruba - Èdè Yorùbá</option>
                                            <option value="zu" {{ $data['code'] == 'zu' ? 'selected' : 'style=display:none' }}>Zulu - isiZulu</option>
                                            </option>
                                        </select>
                                    </div>
                                    <input type="hidden" class="form-control" value="{{$data['status']}}" name="status">
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-secondary"
                                            data-dismiss="modal">{{translate('close')}}</button>
                                    <button type="submit"
                                            class="btn btn-primary">{{translate('update')}}
                                        <i
                                            class="fa fa-plus"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection

@push('script_2')

    <script>
        "use strict";

        $(".language-status-change").on('click', function () {
            let route = $(this).data('route');
            let code = $(this).data('code');
            $.get({
                url: route,
                data: {
                    code: code,
                },
                success: function (data) {

                }
            });
        });

        $(".delete-language").on('click', function (){
            let route = $(this).data('route');

            Swal.fire({
                title: '{{translate('Are you sure to delete this')}}?',
                text: "{{translate('You will not be able to revert this')}}!",
                showCancelButton: true,
                confirmButtonColor: 'primary',
                cancelButtonColor: 'secondary',
                confirmButtonText: '{{translate('Yes, delete it')}}!'
            }).then((result) => {
                if (result.value) {
                    window.location.href = route;
                }
            })
        })
    </script>

@endpush
