{{-- resources/views/reports/contragent.blade.php --}}
@extends('layouts.app', ['title' => 'Отчет Контрагенты #' . $report->id])

@section('meta')
    <meta name="robots" content="noindex,nofollow">
@endsection

@php
    // Функция для перевода ключей
    function translateKey($key) {
        $translations = [
            'address' => 'Адрес',
            'phone' => 'Телефон',
            'email' => 'Email',
            'sum' => 'Сумма',
            'date' => 'Дата',
            'date_start' => 'Дата начала',
            'date_end' => 'Дата окончания',
            'cause' => 'Основание',
            'department' => 'Отдел',
            'number' => 'Номер',
            'company' => 'Организация',
            'post' => 'Должность',
            'inn' => 'ИНН',
            'source' => 'Источник',
            'plate' => 'Госномер',
            'type' => 'Тип',
            'mark' => 'Марка',
            'vin' => 'VIN',
            'vehicle' => 'Авто',
            'airport_from' => 'Аэропорт вылета',
            'airport_to' => 'Аэропорт назначения',
            'type_of_right' => 'Вид права',
            'type_of_premises' => 'Тип помещения',
            'creditrisk' => 'Кредитный риск',
            'additional' => 'Дополнительно',
            'fio' => 'ФИО',
            'prof' => 'Профессия',
            'exp' => 'Опыт',
            'add' => 'Дополнительно',
            'card' => 'Карта',
            'account' => 'Счет',
            'full_name' => 'Полное наименование организации',
            'short_name' => 'Краткое наименование организации',
            'inn' => 'ИНН',
            'ogrn' => 'ОГРН',
            'okpo' => 'Код ОКПО',
            'okpo_ul' => 'Код ОКПО (юр. лицо)',
            'type' => 'Тип субъекта',
            'date_reg' => 'Дата регистрации',
            'address_fact' => 'Фактический адрес',
            'tosp_id' => 'Код ТОСП',
            'update_type' => 'Тип обновления',
            'record_comment' => 'Комментарий к записи',
            'reg_resolution_authority' => 'Орган регистрации',
            'reg_resolution_date' => 'Дата решения о регистрации',
            'reg_resolution_num' => 'Номер решения о регистрации',
            'okved2_fact' => 'Основной ОКВЭД (факт. адрес)',
            'region' => 'Регион',
            'category' => 'Категория должника',
            'place_life' => 'Адрес',
            'contract' => 'Договор',
            'date_contract' => 'Дата договора',
            'startDate' => 'Дата начала',
            'endDate' => 'Дата окончания',
            'datePublish' => 'Дата публикации',
            'number' => 'Номер',
            'isSubleaseContract' => 'Тип договора',
            'lessees' => 'Лизингополучатель',
            'guid' => 'Ссылка',
            'DATA' => 'Дата',
            'DATABEGIN' => 'Дата начала',
            'BIK' => 'БИК',
            'NOMER' => 'Номер решения',
            'KODOSNOV' => 'Основание',
            'SALDOENS' => 'Сумма',
        ];
        
        return $translations[$key] ?? $key;
    }

    // Функция для рекурсивного декодирования юникода
    function unicode_decode($data) {
        if (is_string($data)) {
            return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
                return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
            }, $data);
        } elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = unicode_decode($value);
            }
            return $data;
        } elseif (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->$key = unicode_decode($value);
            }
            return $data;
        }
        return $data;
    }
    
    // Декодируем все данные
    $decodedApiResponses = unicode_decode($report->api_responses ?? []);
    
    $fullName = trim(($report->last_name ?? '') . ' ' . ($report->first_name ?? '') . ' ' . ($report->patronymic ?? ''));
    $fullNameHide = trim(
        preg_replace("/(?<=\w)\w/iu", '*', $report->last_name ?? '') . ' ' . 
        preg_replace("/(?<=\w)\w/iu", '*', $report->first_name ?? '') . ' ' . 
        ($report->patronymic ? preg_replace("/(?<=\w)\w/iu", '*', $report->patronymic) : '')
    );
    
    // Функция для форматирования даты
    function formatDate($date) {
        if (empty($date) || $date === 'null') return '—';
        if (strpos($date, 'T') !== false) {
            return date('d.m.Y', strtotime($date));
        }
        if (strlen($date) == 10 && strpos($date, '-') !== false) {
            return date('d.m.Y', strtotime($date));
        }
        return $date;
    }
    
    // Функция для безопасного получения данных из ответа
    function getResponseData($response) {
        if (!isset($response['response'])) return null;
        
        $data = $response['response'];
        
        // Если это строка, пробуем декодировать JSON
        if (is_string($data) && !empty($data) && $data !== '""') {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            return $data;
        }
        
        return $data;
    }
    
    // Функция для проверки наличия данных
    function hasData($data) {
        if (empty($data)) return false;
        if (is_string($data) && ($data === '""' || $data === '')) return false;
        if (is_array($data) && count($data) === 0) return false;
        return true;
    }
    
    // Функция для получения цвета иконки
    function getIconClass($target, $hasData) {
        if (!$hasData) return 'infos';
        
        return match($target) {
            'cad-org', 'bankrot-org', 'payment-block-org' => 'danger',
            'static-code-org', 'egrul-data-org', 'blacklist-org', 'rosfin-org', 'bad-contragent-org', 'disqualification-org' => 'success',
            default => 'infos'
        };
    }
    
    // Функция для получения названия блока
    function getBlockTitle($target) {
        return match($target) {
            'fssp-org' => 'Сведения о долгах у ФССП',
            'cad-org' => 'Арбитражные суды',
            'bankrot-org' => 'Банкротство',
            'inagent-org' => 'Иноагенты',
            'payment-block-org' => 'Блокировка счетов',
            'disqualification-org' => 'Дисквалификация ФНС',
            'tax-regime-org' => 'Налоговый режим',
            'employee-count-org' => 'Численность сотрудников',
            'sme-reg-org' => 'Реестр МСП',
            'sme-support-org' => 'Поддержка МСП',
            'egrul-data-org' => 'Выписка из ЕГРЮЛ/ЕГРИП',
            'leasing-org' => 'Лизинг',
            'static-code-org' => 'Коды статистики',
            'blacklist-org' => 'Черный список ЦБ',
            'rosfin-org' => 'Террористы и экстремисты',
            'bad-contragent-org' => 'Недобросовестный поставщик',
            'zalog-org' => 'Залог',
            default => $target
        };
    }
@endphp

@section('content')
    @if (Auth::user() && Auth::user()->phone)
        <input type="hidden" id="existPhone" value="1"/>
    @else
        <input type="hidden" id="existPhone" value="0"/>
    @endif
    @if (Auth::user() && Auth::user()->email)
        <input type="hidden" id="existEmail" value="1"/>
    @else
        <input type="hidden" id="existEmail" value="0"/>
    @endif
    <input type="hidden" id="reportSuccess" value="{{ $report->status === 'completed' ? 1 : 0 }}"/>

    {{-- Верхняя панель --}}
    <div class="container order top">
        <section class="top">
            <div class="row no-gutters text-md-left text-center">
                <div class="col-xl-4 col-md-4 col-sm-12">
                    <p class="head">Номер отчета:</p>
                    <p class="text oid_report"> #{{ $report->id }} </p>
                </div>
                <div class="col-xl-3 col-md-3 col-sm-12">
                    <p class="head">Дата и время формирования отчёта:</p>
                    <p class="text">{{ $report->processed_at ? $report->processed_at->format('d.m.Y H:i:s') : 'не готов' }}</p>
                </div>
                <div class="col-xl-2 col-md-2 col-sm-12">
                    <p class="head">Поддержка:</p>
                    <p class="text"><a href="mailto:support@example.com">support@example.com</a></p>
                </div>
                <div class="col-xl-3 col-md-2 col-sm-12">
                    <p class="text text-sm-center text-md-right">
                        <a target="_blank" class='download_report'>Скачать PDF</a>
                    </p>
                </div>
            </div>
        </section>
    </div>

    <div class="container order">
        <div class="row no-gutters">
            {{-- Основной контент --}}
            <div class="col-xl-9 col-lg-9 col-12 pl-sm-0 pl-2 pr-sm-0 pr-2">
                {{-- Карточка с ФИО --}}
                <div class="row no-gutters">
                    <div class="col-12 cards pb-md-0 pb-3">
                        <div class="row family no-gutters">
                            <div class="col">{{ $fullNameHide ?: 'Данные не указаны' }}</div>
                        </div>
                        <div class="row info no-gutters">
                            <div class="col-6">
                                <p class="head">Возраст</p>
                                <p class="text">
                                    @if($report->birth_date)
                                        @php
                                            $age = now()->diffInYears($report->birth_date);
                                            $words = ['год', 'года', 'лет'];
                                            $ageText = $age . ' ' . (
                                                $age % 10 == 1 && $age % 100 != 11 ? $words[0] :
                                                ($age % 10 >= 2 && $age % 10 <= 4 && ($age % 100 < 10 || $age % 100 >= 20) ? $words[1] : $words[2])
                                            );
                                        @endphp
                                        {{ $ageText }}
                                    @else
                                        Не указан
                                    @endif
                                </p>
                            </div>
                            <div class="col-6">
                                <p class="head">Регионы поиска</p>
                                <p class="text">{{ $report->region ?: 'Вся Россия' }}</p>
                            </div>
                        </div>
                        <div class="row footer no-gutters">
                            <div class="col">
                                <p class="head fot">Могут встретиться однофамильцы. При отсутствии данных в блоке(ах) отчета убедитесь, что не была допущена ошибка/опечатка при заполнении формы на сайте.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Общая сводка --}}
                <div class="ancore" id="index_i"></div>
                <section id="index" class="box-card">
                    <div class="row no-gutters">
                        <div class="col-12 back">
                            <div class="row heads no-gutters">Общая сводка</div>
                            
                            @foreach($decodedApiResponses as $target => $response)
                                @php
                                    $title = getBlockTitle($target);
                                    
                                    $rawData = getResponseData($response);
                                    $hasData = hasData($rawData);
                                    
                                    $icon = getIconClass($target, $hasData);
                                    
                                    $displayText = $hasData ? 'Найдено' : 'Нет данных';
                                    
                                    // Подсчет количества для некоторых типов
                                    if ($hasData) {
                                        if ($target === 'cad-org' && is_array($rawData)) {
                                            $displayText = count($rawData) . ' ' . trans_choice('дело|дела|дел', count($rawData));
                                        } elseif ($target === 'leasing-org' && is_array($rawData)) {
                                            $displayText = count($rawData) . ' ' . trans_choice('договор|договора|договоров', count($rawData));
                                        } elseif ($target === 'payment-block-org' && isset($rawData['rows']) && is_array($rawData['rows'])) {
                                            $cnt = count($rawData['rows']);
                                            $displayText = $cnt . ' ' . trans_choice('блокировка|блокировки|блокировок', $cnt);
                                        } elseif ($target === 'static-code-org' && is_array($rawData)) {
                                            $displayText = '1 запись';
                                        } elseif ($target === 'bankrot-org' && is_array($rawData)) {
                                            $cnt = count($rawData);
                                            $displayText = $cnt . ' ' . trans_choice('дело|дела|дел', $cnt);
                                        } elseif ($target === 'zalog-org' && is_array($rawData)) {
                                            $cnt = count($rawData);
                                            $displayText = $cnt . ' ' . trans_choice('запись|записи|записей', $cnt);
                                        }
                                    }
                                @endphp
                                
                                <a style="color: unset;" href="#{{ $target }}">
                                    <div class="row infos no-gutters">
                                        <div class="col-md-6 col-sm-12 head">{{ $title }}:</div>
                                        <div class="col-md-6 col-sm-12 text">
                                            <span class="status-icon status-{{ $icon }}"></span>
                                            <b>{{ $displayText }}</b>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- Детальные блоки --}}
                @foreach($decodedApiResponses as $target => $response)
                    @php
                        $targetName = getBlockTitle($target);
                        
                        $rawData = getResponseData($response);
                        $hasData = hasData($rawData);
                        
                        $icon = getIconClass($target, $hasData);
                    @endphp
                    
                    <div class="ancore" id="{{ $target }}"></div>
                    <section id="{{ $target }}_r">
                       <div class="row no-gutters">
                            <div class="col-12">
                                <div class="white-block">
                                    {{-- Заголовок блока с иконкой и названием --}}
                                    <div class="block-header">
                                        <span class="status-icon status-{{ $icon }}"></span>
                                        <h3 class="order">{{ $targetName }}</h3>
                                    </div>
                                    
                                    @if(!$hasData)
                                        {{-- НЕТ ДАННЫХ --}}
                                        <div class="row no-gutters information">
                                            <div class="col-12">
                                                <div class="left-side">
                                                    <p class="text">Нет данных по этому запросу</p>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        {{-- ЕСТЬ ДАННЫЕ --}}
                                        
                                        {{-- СТАТИСТИЧЕСКИЕ КОДЫ --}}
                                        @if($target === 'static-code-org')
                                            @php
                                                $staticData = is_array($rawData) ? (isset($rawData[0]) ? $rawData[0] : $rawData) : [];
                                            @endphp
                                            
                                            @if(!empty($staticData))
                                                <div class="row no-gutters head infos" data-target="#static-code-org-1">Общая сводка</div>
                                                <div id="static-code-org-1" class="accordion collapse show">
                                                    <div class="row no-gutters information">
                                                        <div class="col-md-6">
                                                            <div class="right-side">
                                                                <p class="header">Полное наименование организации:</p>
                                                                <p class="text">{{ $staticData['name'] ?? 'Отсутствует' }}</p>
                                                            </div>
                                                            <div class="right-side">
                                                                <p class="header">Краткое наименование организации:</p>
                                                                <p class="text">{{ $staticData['short_name'] ?? 'Отсутствует' }}</p>
                                                            </div>
                                                            <div class="right-side">
                                                                <p class="header">Тип субъекта:</p>
                                                                <p class="text">{{ $staticData['type'] ?? 'Отсутствует' }}</p>
                                                            </div>
                                                            <div class="right-side">
                                                                <p class="header">Код ОКПО:</p>
                                                                <p class="text">{{ $staticData['okpo'] ?? 'Отсутствует' }}</p>
                                                            </div>
                                                            <div class="right-side">
                                                                <p class="header">Код ОКПО (юр. лицо):</p>
                                                                <p class="text">{{ $staticData['okpo_ul'] ?? 'Отсутствует' }}</p>
                                                            </div>
                                                            <div class="right-side">
                                                                <p class="header">Дата регистрации:</p>
                                                                <p class="text">{{ isset($staticData['date_reg']) ? date('d.m.Y', strtotime($staticData['date_reg'])) : 'Отсутствует' }}</p>
                                                            </div>
                                                            <div class="right-side">
                                                                <p class="header">ИНН:</p>
                                                                <p class="text">{{ $staticData['inn'] ?? 'Отсутствует' }}</p>
                                                            </div>
                                                            <div class="right-side">
                                                                <p class="header">ОГРН:</p>
                                                                <p class="text">{{ $staticData['ogrn'] ?? 'Отсутствует' }}</p>
                                                            </div>
                                                            <div class="right-side">
                                                                <p class="header">Фактический адрес:</p>
                                                                <p class="text">{{ $staticData['address_fact'] ?? 'Отсутствует' }}</p>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-md-6">
                                                            @if(isset($staticData['okato_reg']) && is_array($staticData['okato_reg']))
                                                            <div class="right-side">
                                                                <p class="header">Наименование ОКАТО (регистрация):</p>
                                                                <p class="text">{{ $staticData['okato_reg']['name'] ?? 'Отсутствует' }}</p>
                                                            </div>
                                                            <div class="right-side">
                                                                <p class="header">Код ОКАТО (регистрация):</p>
                                                                <p class="text">{{ $staticData['okato_reg']['code'] ?? 'Отсутствует' }}</p>
                                                            </div>
                                                            @endif
                                                            
                                                            @if(isset($staticData['oktmo_reg']) && is_array($staticData['oktmo_reg']))
                                                            <div class="right-side">
                                                                <p class="header">Код ОКТМО (регистрация):</p>
                                                                <p class="text">{{ $staticData['oktmo_reg']['code'] ?? 'Отсутствует' }}</p>
                                                            </div>
                                                            <div class="right-side">
                                                                <p class="header">Наименование ОКТМО (регистрация):</p>
                                                                <p class="text">{{ $staticData['oktmo_reg']['name'] ?? 'Отсутствует' }}</p>
                                                            </div>
                                                            @endif
                                                            
                                                            @if(isset($staticData['okopf']) && is_array($staticData['okopf']))
                                                            <div class="right-side">
                                                                <p class="header">Организационно-правовая форма (ОКОПФ):</p>
                                                                <p class="text">{{ $staticData['okopf']['name'] ?? 'Отсутствует' }}</p>
                                                            </div>
                                                            @endif
                                                            
                                                            @if(isset($staticData['okfs']) && is_array($staticData['okfs']))
                                                            <div class="right-side">
                                                                <p class="header">Код формы собственности (ОКФС):</p>
                                                                <p class="text">{{ $staticData['okfs']['name'] ?? 'Отсутствует' }}</p>
                                                            </div>
                                                            @endif
                                                            
                                                            @if(isset($staticData['okogu']) && is_array($staticData['okogu']))
                                                            <div class="right-side">
                                                                <p class="header">Категория учредителя (ОКОГУ):</p>
                                                                <p class="text">{{ $staticData['okogu']['name'] ?? 'Отсутствует' }}</p>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-12 p-0">
                                                        <div class="full-width-divider"></div>
                                                    </div>
                                                </div>
                                            @endif
                                        
                                        {{-- АРБИТРАЖНЫЕ ДЕЛА --}}
                                        @elseif($target === 'cad-org' && is_array($rawData))
                                            <div class="mt-4">
                                                @foreach($rawData as $key => $case)
                                                    @if(is_array($case) && count($case) >= 4)
                                                        <div class="row no-gutters information" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                                                            <div class="col-md-6">
                                                                <div class="left-side">
                                                                    <p class="header">Дата / номер дела:</p>
                                                                    <p class="text">{{ $case[0] ?? '' }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="left-side">
                                                                    <p class="header">Судья / суд:</p>
                                                                    <p class="text">{{ $case[1] ?? '' }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="left-side">
                                                                    <p class="header">Истец:</p>
                                                                    <p class="text">{{ is_array($case[2] ?? '') ? json_encode($case[2], JSON_UNESCAPED_UNICODE) : ($case[2] ?? '') }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="left-side">
                                                                    <p class="header">Ответчик:</p>
                                                                    <p class="text">{{ is_array($case[3] ?? '') ? json_encode($case[3], JSON_UNESCAPED_UNICODE) : ($case[3] ?? '') }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        
                                        {{-- ЛИЗИНГ --}}
                                        @elseif($target === 'leasing-org' && is_array($rawData))
                                            <div class="mt-4">
                                                @foreach($rawData as $lease)
                                                    @php 
                                                        if (is_string($lease)) {
                                                            $lease = json_decode($lease, true) ?: [];
                                                        }
                                                        $lease = (array)$lease; 
                                                    @endphp
                                                    
                                                    @if(!empty($lease))
                                                        <div class="row no-gutters information" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                                                            <div class="col-md-12" style="margin-bottom: 10px">
                                                                <div class="left-side">
                                                                    <p class="header">Дата публикации:</p>
                                                                    <p class="text">{{ formatDate($lease['datePublish'] ?? '') }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="left-side">
                                                                    <p class="header">Договор №:</p>
                                                                    <p class="text">{{ $lease['contract'] ?? '' }}</p>
                                                                </div>
                                                                <div class="left-side">
                                                                    <p class="header">Дата договора:</p>
                                                                    <p class="text">{{ formatDate($lease['date_contract'] ?? '') }}</p>
                                                                </div>
                                                                <div class="left-side">
                                                                    <p class="header">Срок лизинга:</p>
                                                                    <p class="text">{{ formatDate($lease['startDate'] ?? '') }} — {{ formatDate($lease['endDate'] ?? '') }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="left-side">
                                                                    <p class="header">Тип договора:</p>
                                                                    <p class="text">{{ $lease['isSubleaseContract'] ?? 'Обычный договор лизинга' }}</p>
                                                                </div>
                                                                <div class="left-side">
                                                                    <p class="header">Номер в реестре:</p>
                                                                    <p class="text">{{ $lease['number'] ?? '' }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="left-side">
                                                                    <p class="header">Лизингополучатель:</p>
                                                                    <p class="text">
                                                                        @if(!empty($lease['lessees']) && is_array($lease['lessees']))
                                                                            @foreach($lease['lessees'] as $lessee)
                                                                                {{ $lessee['fullName'] ?? '' }} 
                                                                                @if(!empty($lessee['inn'])) (ИНН: {{ $lessee['inn'] }}) @endif
                                                                            @endforeach
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            @if(!empty($lease['guid']))
                                                            <div class="col-md-12">
                                                                <div class="left-side">
                                                                    <p class="header">Ссылка:</p>
                                                                    <p class="text"><a href="https://fedresurs.ru/sfactmessages/{{ $lease['guid'] }}" target="_blank">Перейти на fedresurs.ru</a></p>
                                                                </div>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        
                                        {{-- БЛОКИРОВКА СЧЕТОВ --}}
                                        @elseif($target === 'payment-block-org' && isset($rawData['rows']) && is_array($rawData['rows']))
                                            <div class="mt-4">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered bg-white">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Дата</th>
                                                                <th>Дата начала</th>
                                                                <th>БИК</th>
                                                                <th>Номер решения</th>
                                                                <th>Основание</th>
                                                                <th>Сумма</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($rawData['rows'] as $index => $row)
                                                                @php $row = (array)$row; @endphp
                                                                <tr>
                                                                    <td>{{ $index + 1 }}</td>
                                                                    <td>{{ $row['DATA'] ?? '' }}</td>
                                                                    <td>{{ $row['DATABEGIN'] ?? '' }}</td>
                                                                    <td>{{ $row['BIK'] ?? '' }}</td>
                                                                    <td>{{ $row['NOMER'] ?? '' }}</td>
                                                                    <td>
                                                                        @php
                                                                            $kod = $row['KODOSNOV'] ?? '';
                                                                            if($kod == '01') echo 'Не исполнение требования';
                                                                            elseif($kod == '02') echo 'Непредставление декларации';
                                                                            elseif($kod == '03') echo 'Необеспечение получения требований';
                                                                            else echo $kod;
                                                                        @endphp
                                                                    </td>
                                                                    <td>{{ $row['SALDOENS'] ?? '' }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        
                                        {{-- БАНКРОТСТВО --}}
                                        @elseif($target === 'bankrot-org' && is_array($rawData))
                                            @php
                                                $bankrotItem = isset($rawData[0]) ? $rawData[0] : $rawData;
                                                if (is_string($bankrotItem)) {
                                                    $bankrotItem = json_decode($bankrotItem, true) ?: [];
                                                }
                                                $bankrotData = (array)$bankrotItem;
                                            @endphp
                                            
                                            @if(!empty($bankrotData))
                                                <div class="mt-4">
                                                    <div class="row no-gutters information">
                                                        <div class="col-md-6">
                                                            <div class="left-side">
                                                                <p class="header">Полное наименование:</p>
                                                                <p class="text">{{ $bankrotData['full_name'] ?? '' }}</p>
                                                            </div>
                                                            <div class="left-side">
                                                                <p class="header">Краткое наименование:</p>
                                                                <p class="text">{{ $bankrotData['short_name'] ?? '' }}</p>
                                                            </div>
                                                            <div class="left-side">
                                                                <p class="header">ИНН:</p>
                                                                <p class="text">{{ $bankrotData['inn'] ?? '' }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="left-side">
                                                                <p class="header">ОГРН:</p>
                                                                <p class="text">{{ $bankrotData['ogrnip'] ?? '' }}</p>
                                                            </div>
                                                            <div class="left-side">
                                                                <p class="header">Регион:</p>
                                                                <p class="text">{{ $bankrotData['region'] ?? '' }}</p>
                                                            </div>
                                                            <div class="left-side">
                                                                <p class="header">Категория должника:</p>
                                                                <p class="text">{{ $bankrotData['category'] ?? '' }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="left-side">
                                                                <p class="header">Адрес:</p>
                                                                <p class="text">{{ $bankrotData['place_life'] ?? '' }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        
                                        {{-- ЗАЛОГ --}}
                                        @elseif($target === 'zalog-org' && is_array($rawData))
                                            <div class="mt-4">
                                                @foreach($rawData as $zalog)
                                                    @php 
                                                        if (is_string($zalog)) {
                                                            $zalog = json_decode($zalog, true) ?: [];
                                                        }
                                                        $zalog = (array)$zalog; 
                                                    @endphp
                                                    
                                                    @if(!empty($zalog))
                                                        <div class="row no-gutters information" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                                                            <div class="col-md-6">
                                                                <div class="left-side">
                                                                    <p class="header">Номер:</p>
                                                                    <p class="text">{{ $zalog['number'] ?? '' }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="left-side">
                                                                    <p class="header">Дата публикации:</p>
                                                                    <p class="text">{{ formatDate($zalog['datePublish'] ?? '') }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="left-side">
                                                                    <p class="header">Залогодатель:</p>
                                                                    <p class="text">{{ $zalog['pawnor'] ?? '' }}</p>
                                                                </div>
                                                            </div>
                                                            @if(!empty($zalog['guid']))
                                                            <div class="col-md-12">
                                                                <div class="left-side">
                                                                    <p class="header">Ссылка:</p>
                                                                    <p class="text"><a href="https://fedresurs.ru/sfactmessages/{{ $zalog['guid'] }}" target="_blank">Перейти на fedresurs.ru</a></p>
                                                                </div>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        
                                        {{-- ЧЕРНЫЕ СПИСКИ, Росфин и другие с данными --}}
                                        @else
                                            <div class="row no-gutters information">
                                                <div class="col-12">
                                                    <div class="left-side">
                                                        <p class="text">Данные найдены в системе</p>
                                                        @if(is_array($rawData) && count($rawData) > 0)
                                                            <pre class="bg-light p-3 rounded" style="max-height: 300px; overflow: auto;"><code>{{ json_encode($rawData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                    
                                    {{-- Технические данные для отладки --}}
                                    <div class="mt-4">
                                        <small class="text-muted">
                                            <a href="#" onclick="event.preventDefault(); this.nextElementSibling.classList.toggle('d-none'); return false;">
                                                Показать технические данные
                                            </a>
                                        </small>
                                        <div class="d-none mt-2">
                                            <pre class="bg-dark text-light p-3 rounded" style="max-height: 300px; overflow: auto;"><code>{{ json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                @endforeach

                {{-- Блок "Обезопасьте себя от проблем!" --}}
                <section class="alert">
                    <div class="row align-items-center text-lg-left text-center">
                        <div class="col-lg-7 col-md-12">
                            <p class="head">Обезопасьте себя от проблем!</p>
                            <p class="text">Проверка человека не займет много времени, но позволит выявить и предотвратить попытку мошенничества.</p>
                        </div>
                        <div class="col-12 d-lg-none d-md-block pt-4"></div>
                        <div class="col-lg-5 col-md-12">
                            <a href="/#form-top" class="button_alert py-3 px-sm-5 px-4">Проверить человека</a>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Сайдбар --}}
            <div class="col-xl-3 col-lg-3 pr-xl-0 pr-md-2 d-xl-block d-lg-block  d-md-block">
                <div class="sidebar" id="ancor" style="position: sticky; top: 85px;">
                    <h3>Оглавление</h3>
                    <ul>
                        @foreach($decodedApiResponses as $target => $response)
                            @php
                                $title = getBlockTitle($target);
                                $rawData = getResponseData($response);
                                $hasData = hasData($rawData);
                                $iconClass = getIconClass($target, $hasData);
                            @endphp
                            <a href="#{{ $target }}">
                                <li id="{{ $target }}">
                                    <span class="status-icon-small status-{{ $iconClass }}"></span>
                                    {{ $title }}
                                </li>
                            </a>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Кнопка "Наверх" --}}
    <span class="totop show">
        <svg class="totop_arrow" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 19V5M5.5 11.5L12 5l6.5 6.5" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </span>
@endsection

@push('styles')
<style>
    .container.order { max-width: 1400px; margin: 0 auto; padding: 0 15px; }
    .container.order.top { background: linear-gradient(90deg, #0d6efd 0%, #0b5ed7 100%); color: white; border-radius: 8px 8px 0 0; padding: 15px; }
    .container.order.top .head { color: rgba(255,255,255,0.8); font-size: 14px; margin-bottom: 5px; }
    .container.order.top .text { color: white; font-size: 16px; font-weight: 600; }
    .container.order.top a { color: white; text-decoration: underline; }
    .download_report { display: inline-block; padding: 8px 16px; background: rgba(255,255,255,0.2); border-radius: 4px; color: white; text-decoration: none; font-weight: 500; transition: all 0.2s; }
    .cards { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 20px; padding: 20px; }
    .row.family { border-bottom: 2px solid #0d6efd; padding-bottom: 10px; margin-bottom: 15px; }
    .row.family .col { font-size: 24px; font-weight: 600; color: #333; }
    .row.info { margin-bottom: 15px; }
    .row.info .head { color: #6c757d; font-size: 14px; margin-bottom: 5px; }
    .row.info .text { color: #333; font-size: 16px; font-weight: 500; }
    .row.footer .head.fot { color: #6c757d; font-size: 13px; font-style: italic; }
    .white-block { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 20px 0; padding: 20px; }
    .row.head.infos, .row.head.success, .row.head.danger { border-bottom: 2px solid #0d6efd; padding-bottom: 10px; margin-bottom: 20px; }
    .row.head.infos span.infos, .row.head.success span.success, .row.head.danger span.danger { font-size: 18px; font-weight: 600; color: #333; }
    .row.head.danger { border-bottom-color: #dc3545; }
    .row.head.success { border-bottom-color: #28a745; }
    .head-img { width: 24px; height: 24px; margin-right: 10px; display: inline-block; }
    h3.order { font-size: 20px; font-weight: 600; color: #333; margin-bottom: 15px; display: inline-block; }
    .row.infos { padding: 10px 0; border-bottom: 1px solid #eee; }
    .row.infos .head { color: #6c757d; }
    .row.infos .text { color: #333; font-weight: 500; }
    .back { background: #f8f9fa; padding: 20px; border-radius: 8px; }
    .row.heads { font-size: 18px; font-weight: 600; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #0d6efd; }
    .sidebar { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); position: sticky; top: 85px; }
    .sidebar h3 { font-size: 18px; font-weight: 600; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #0d6efd; }
    .sidebar ul { list-style: none; padding: 0; margin: 0; }
    .sidebar ul a { color: #333; text-decoration: none; display: block; padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
    .sidebar ul a:hover li { color: #0d6efd; }
    .sidebar ul a.active li { color: #0d6efd; font-weight: 600; }
    .sidebar ul a li { display: flex; align-items: center; gap: 8px; }
    .left-side, .right-side { padding: 5px 0; }
    .left-side .header, .right-side .header { font-weight: 600; color: #495057; margin-bottom: 3px; font-size: 14px; }
    .left-side .text, .right-side .text { color: #333; font-size: 14px; word-break: break-word; }
    .full-width-divider { border-bottom: 1px solid #eaeaea; width: 100%; margin: 15px 0; }
    .totop { position: fixed; bottom: 30px; right: 30px; width: 50px; height: 50px; background: #0d6efd; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; opacity: 0; transition: opacity 0.3s; z-index: 1000; }
    .totop.show { opacity: 1; }
    .totop svg { width: 30px; height: 30px; stroke: white; }
    section.alert { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; padding: 30px; margin-top: 30px; }
    .button_alert { display: inline-block; background: white; color: #764ba2; border-radius: 50px; font-weight: 600; text-decoration: none; transition: all 0.3s; }
    @media (max-width: 768px) { .container.order { padding: 0 10px; } .row.family .col { font-size: 20px; } }
    .table-responsive { overflow-x: auto; }
    .table-sm th, .table-sm td { padding: 0.5rem; white-space: nowrap; }
    .d-none { display: none !important; }
    
    /* CSS-иконки вместо изображений */
    .status-icon {
        display: inline-block;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        margin-right: 5px;
    }
    
    .status-icon-small {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 5px;
    }
    
    .status-infos {
        background-color: #17a2b8;
    }
    
    .status-success {
        background-color: #28a745;
    }
    
    .status-danger {
        background-color: #dc3545;
    }
    
    /* Для блока общей сводки */
    .row.infos .text {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    /* Для заголовков блоков */
    .row.no-gutters .head-img.status-icon {
        margin-right: 10px;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(window).on('load', function() {
        $(window).scroll(function() {
            if ($(this).scrollTop() > 100) $('.totop').addClass('show');
            else $('.totop').removeClass('show');
        });

        $('.totop').click(function() {
            $('html, body').animate({scrollTop: 0}, 300);
        });

        function setActiveAnchor() {
            let scrollPos = $(document).scrollTop();
            let found = false;
            
            $('#ancor ul a').each(function () {
                let currLink = $(this);
                let refId = currLink.attr('href');
                let refElement = $(refId + '_r');
                
                if (refElement.length) {
                    let elementTop = refElement.position().top;
                    let elementBottom = elementTop + refElement.height();
                    
                    if (elementTop <= scrollPos + 100 && elementBottom > scrollPos + 100) {
                        $('#ancor ul a').removeClass('active');
                        currLink.addClass('active');
                        found = true;
                    }
                }
            });
            
            // Если ничего не найдено, активируем первый пункт
            if (!found && $('#ancor ul a').first().length) {
                $('#ancor ul a').removeClass('active');
                $('#ancor ul a').first().addClass('active');
            }
        }
        
        $(window).on('scroll', setActiveAnchor);
        setTimeout(setActiveAnchor, 100);

        $('a[href="#"]').on('click', function(e) { e.preventDefault(); });
    });
</script>
@endpush