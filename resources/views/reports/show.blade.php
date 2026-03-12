@extends('layouts.app', ['title' => 'Страница отчёта #' . $report->id])

@section('meta')
    <meta name="robots" content="noindex,nofollow">
@endsection

@section('content')
    @php
        // Функция для перевода ключей
        function translateKey($key) {
            $translations = [
                // Только английские ключи
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
            ];
            
            // Если ключ есть в переводе - возвращаем перевод
            if (isset($translations[$key])) {
                return $translations[$key];
            }
            
            // Иначе возвращаем как есть (ключ уже на русском)
            return $key;
        }
    @endphp

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

    <div class="container order top">
        <section class="top">
            <div class="row no-gutters text-md-left text-center">
                <div class="col-xl-4 col-md-4 col-sm-12">
                    <p class="head">Номер отчета:</p>
                    <p class="text oid_report"> #{{ $report->id }} </p>
                </div>
                <div class="w-100 d-md-none d-block pt-2"></div>
                <div class="col-xl-3 col-md-3 col-sm-12">
                    <p class="head">Дата и время формирования отчёта:</p>
                    <p class="text">
                        {{ $report->processed_at ? $report->processed_at->format('d.m.Y H:i:s') : 'не готов' }}
                    </p>
                </div>
                <div class="w-100 d-md-none d-block pt-2"></div>
                <div class="col-xl-2 col-md-2 col-sm-12">
                    <p class="head">Поддержка:</p>
                    <p class="text"><a href="mailto:support@example.com">support@example.com</a></p>
                </div>

                <div class="w-100 d-md-none d-block pt-2"></div>
                <div class="col-xl-3 col-md-2 col-sm-12">
                    <p class="text text-sm-center text-md-right">
                        <a target="_blank" class='download_report' href="#">Скачать PDF</a>
                    </p>
                </div>
            </div>
        </section>
    </div>

    <div class="container order">
        <div class="row no-gutters">
            <div class="col-xl-12 col-lg-12 col-12 pl-sm-0 pl-2 pr-sm-0 pr-2">
                <div class="row no-gutters">
                    <div class="col-12 cards pb-md-0 pb-3">
                        @php
                            $fullName = trim(($report->last_name ?? '') . ' ' . ($report->first_name ?? '') . ' ' . ($report->patronymic ?? ''));
                            $fullNameHide = trim(
                                preg_replace("/(?<=\w)\w/iu", '*', $report->last_name ?? '') . ' ' . 
                                preg_replace("/(?<=\w)\w/iu", '*', $report->first_name ?? '') . ' ' . 
                                ($report->patronymic ? preg_replace("/(?<=\w)\w/iu", '*', $report->patronymic) : '')
                            );
                        @endphp
                        <div class="row family no-gutters">
                            <div class="col">
                                {{ $fullNameHide ?: 'Данные не указаны' }}
                            </div>
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
                                <p class="text">
                                    {{ $report->region ?: 'Вся Россия' }}
                                </p>
                            </div>
                        </div>

                        <div class="row footer no-gutters">
                            <div class="col">
                                <p class="head fot">Могут встретиться однофамильцы. При отсутствии данных в
                                    блоке(ах) отчета убедитесь, что не была допущена ошибка/опечатка при заполнении формы на
                                    сайте.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Блоки данных из update_array_blocks -->
                @if(isset($updateArrayBlocks) && !empty($updateArrayBlocks))
                    @foreach($updateArrayBlocks as $blockIndex => $blockItems)
                        @if(!empty($blockItems))
                            <div class="ancore" id="block-{{ $blockIndex }}"></div>
                            <section id="block-{{ $blockIndex }}_r">
                                <div class="white-block">
                                    <div class="row no-gutters head infos">
                                        <span class="infos">
                                            @if(isset($d2OptionsOfBlocks[$blockIndex]['title']))
                                                {{ $d2OptionsOfBlocks[$blockIndex]['title'] }}
                                            @else
                                                Блок данных #{{ $blockIndex + 1 }}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="row no-gutters information">
                                        @if(!empty($blockItems))
                                            <table class="report-data-table">
                                                <thead>
                                                    <tr>
                                                        @foreach(array_keys($blockItems[0] ?? []) as $field)
                                                            @if(!in_array($field, ['data', 'block']))
                                                                <th>{{ translateKey($field) }}</th>
                                                            @endif
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($blockItems as $itemIndex => $item)
                                                        <tr data-bs-toggle="collapse"
                                                            data-bs-target="#clps-{{ $blockIndex }}-{{ $itemIndex }}"
                                                            class="accordion-toggle collapsed" aria-expanded="false">
                                                            @foreach($item as $key => $value)
                                                                @if(!in_array($key, ['data', 'block']))
                                                                    @php
                                                                        $displayValue = $value;
                                                                        // Звездочки только для адресов
                                                                        if(is_string($value) && in_array($key, ['address', 'Адрес', 'адрес', 'Адрес регистрации', 'Адрес проживания', 'Полный адрес', 'Адрес_проживания', 'Адрес_регистрации', 'Адрес со слов', 'Адрес факт.', 'Адрес предыдущий', 'Адрес 2', 'Адрес места жительства', 'Субъект РФ'])) {
                                                                            $len = mb_strlen($value);
                                                                            $displayValue = mb_substr($value, 0, ceil($len * 0.8)) . str_repeat('*', $len - ceil($len * 0.8));
                                                                        }
                                                                    @endphp
                                                                    <td>{{ $displayValue }}</td>
                                                                @endif
                                                            @endforeach
                                                        </tr>
                                                        <tr>
                                                            <td colspan="10" class="hiddenRow">
                                                                <div class="hidden-row-grid collapse"
                                                                     id="clps-{{ $blockIndex }}-{{ $itemIndex }}">
                                                                    @if(isset($item['data']) && is_array($item['data']))
                                                                        @foreach($item['data'] as $dataKey => $dataValue)
                                                                            @if(!in_array($dataKey, ['ДАТА РОЖДЕНИЯ', 'День рождения', 'ИМЯ', 'ФИО']))
                                                                                @php
                                                                                    $displayValue = $dataValue;
                                                                                    // Звездочки только для адресов
                                                                                    if(is_string($dataValue) && in_array($dataKey, ['Адрес', 'адрес', 'Адрес регистрации', 'Адрес проживания', 'Полный адрес', 'Адрес_проживания', 'Адрес_регистрации', 'Адрес со слов', 'Адрес факт.', 'Адрес предыдущий', 'Адрес 2', 'Адрес места жительства'])) {
                                                                                        $len = mb_strlen($dataValue);
                                                                                        $displayValue = mb_substr($dataValue, 0, ceil($len * 0.8)) . str_repeat('*', $len - ceil($len * 0.8));
                                                                                    }
                                                                                @endphp
                                                                                <span>{{ translateKey($dataKey) }}:</span>
                                                                                <span>{{ !empty($displayValue) ? $displayValue : 'Нет данных' }}</span>
                                                                            @endif
                                                                        @endforeach
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <div class="row no-gutters head infos">
                                                <span class="infos">Нет данных в этом блоке</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </section>
                        @endif
                    @endforeach
                @endif

                <!-- Скоринг негатива -->
                @if(isset($data) && !empty($data))
                    <div class="row no-gutters">
                        <div class="col-12">
                            <div class="row no-gutters" style="margin-top: 15px"><h3 class="order">Скоринг негатива</h3></div>
                            <div class="white-block">
                                @php
                                    $blocksMore15 = false;
                                    $counter = 0;
                                    $negativeBlocks = [];
                                    $otherBlocks = [];
                                    
                                    // Разделяем блоки на негативные и остальные
                                    foreach($data as $item) {
                                        if(isset($item['Источник'])) {
                                            $counter++;
                                            if ($counter > 15) {
                                                $blocksMore15 = true;
                                                $negativeBlocks[] = $item;
                                            } else {
                                                $otherBlocks[] = $item;
                                            }
                                        }
                                    }
                                @endphp
                                
                                <!-- Сначала показываем первые 15 блоков -->
                                @foreach($otherBlocks as $key => $item)
                                    <div class="row no-gutters head additional-block danger collapsed"
                                         data-bs-toggle="collapse"
                                         data-bs-target="#clps-3-{{ $loop->index }}"
                                         aria-expanded="false">
                                        <span>Блок: {{ $item['Источник'] }}</span>
                                    </div>
                                    <div class="collapse accordion-danger" id="clps-3-{{ $loop->index }}">
                                        <div class="row no-gutters information">
                                            @foreach($item as $key2 => $item2)
                                                @if(!in_array($key2, ['Источник', 'ИСТОЧНИК', 'ИМЯ', 'ФИО', 'ДАТА РОЖДЕНИЯ', 'День рождения']))
                                                    <div class="col-md-12">
                                                        <div class="left-side">
                                                            <p class="header">{{ translateKey($key2) }}:</p>
                                                            <p class="text">
                                                                @if(in_array($key2, ['Адрес', 'АДРЕС', 'Адрес регистрации', 'Адрес проживания', 'Полный адрес']))
                                                                    @php
                                                                        $len = mb_strlen($item2);
                                                                        $displayValue = mb_substr($item2, 0, ceil($len * 0.8)) . str_repeat('*', $len - ceil($len * 0.8));
                                                                    @endphp
                                                                    {!! $displayValue !!}
                                                                @else
                                                                    @php
                                                                        $item2 = str_replace(mb_strtoupper($fullName), $fullNameHide, mb_strtoupper($item2));
                                                                        $item2 = str_replace(mb_strtoupper($report->last_name ?? ''), preg_replace("/(?<=\w)\w/iu", '*', $report->last_name ?? ''), $item2);
                                                                    @endphp
                                                                    {!! str_replace(',', ', ', $item2) !!}
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                                
                                <!-- Затем скрытые блоки (если их больше 15) -->
                                @if($blocksMore15)
                                    @foreach($negativeBlocks as $key => $item)
                                        <div class="row no-gutters head additional-block danger collapsed danger-additional-hidden"
                                             data-bs-toggle="collapse"
                                             data-bs-target="#clps-3-hidden-{{ $loop->index }}"
                                             aria-expanded="false">
                                            <span>Блок: {{ $item['Источник'] }}</span>
                                        </div>
                                        <div class="collapse accordion-danger danger-additional-hidden" id="clps-3-hidden-{{ $loop->index }}">
                                            <div class="row no-gutters information">
                                                @foreach($item as $key2 => $item2)
                                                    @if(!in_array($key2, ['Источник', 'ИСТОЧНИК', 'ИМЯ', 'ФИО', 'ДАТА РОЖДЕНИЯ', 'День рождения']))
                                                        <div class="col-md-12">
                                                            <div class="left-side">
                                                                <p class="header">{{ translateKey($key2) }}:</p>
                                                                <p class="text">
                                                                    @if(in_array($key2, ['Адрес', 'АДРЕС', 'Адрес регистрации', 'Адрес проживания', 'Полный адрес']))
                                                                        @php
                                                                            $len = mb_strlen($item2);
                                                                            $displayValue = mb_substr($item2, 0, ceil($len * 0.8)) . str_repeat('*', $len - ceil($len * 0.8));
                                                                        @endphp
                                                                        {!! $displayValue !!}
                                                                    @else
                                                                        @php
                                                                            $item2 = str_replace(mb_strtoupper($fullName), $fullNameHide, mb_strtoupper($item2));
                                                                            $item2 = str_replace(mb_strtoupper($report->last_name ?? ''), preg_replace("/(?<=\w)\w/iu", '*', $report->last_name ?? ''), $item2);
                                                                        @endphp
                                                                        {!! str_replace(',', ', ', $item2) !!}
                                                                    @endif
                                                                </p>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                    <div class="row no-gutters head additional-block danger danger-block-additional"
                                         data-target="#clps-additional-blocks"
                                         aria-expanded="false">
                                        <span class="danger-block-arrow">Показать все блоки</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Скоринг телефона -->
                <div class="row no-gutters">
                    <div class="col-12">
                        <div class="row no-gutters" style="margin-top: 15px"><h3 class="order">Скоринг телефона</h3></div>
                        <div class="white-block">
                            <div class="row no-gutters head infos">
                                <span>Информация по телефону будет доступна в следующей версии</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <span class="totop show">
        <svg class="totop_arrow" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 19V5M5.5 11.5L12 5l6.5 6.5" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </span>
@endsection

@push('styles')
<style>
    .container.order {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 15px;
    }
    .container.order.top {
        background: linear-gradient(90deg, #0d6efd 0%, #0b5ed7 100%);
        color: white;
        border-radius: 8px 8px 0 0;
        padding: 15px;
    }
    .container.order.top .head {
        color: rgba(255,255,255,0.8);
        font-size: 14px;
        margin-bottom: 5px;
    }
    .container.order.top .text {
        color: white;
        font-size: 16px;
        font-weight: 600;
    }
    .container.order.top a {
        color: white;
        text-decoration: underline;
    }
    .container.order.top a:hover {
        color: rgba(255,255,255,0.9);
    }
    .download_report {
        display: inline-block;
        padding: 8px 16px;
        background: rgba(255,255,255,0.2);
        border-radius: 4px;
        color: white;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s;
    }
    .download_report:hover {
        background: rgba(255,255,255,0.3);
        color: white;
    }
    .cards {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-top: 20px;
        padding: 20px;
    }
    .row.family {
        border-bottom: 2px solid #0d6efd;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }
    .row.family .col {
        font-size: 24px;
        font-weight: 600;
        color: #333;
    }
    .row.info {
        margin-bottom: 15px;
    }
    .row.info .head {
        color: #6c757d;
        font-size: 14px;
        margin-bottom: 5px;
    }
    .row.info .text {
        color: #333;
        font-size: 16px;
        font-weight: 500;
    }
    .row.footer .head.fot {
        color: #6c757d;
        font-size: 13px;
        font-style: italic;
    }
    .white-block {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin: 20px 0;
        padding: 20px;
    }
    .row.head.infos {
        border-bottom: 2px solid #0d6efd;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }
    .row.head.infos span.infos {
        font-size: 18px;
        font-weight: 600;
        color: #333;
    }
    .report-data-table {
        width: 100%;
        border-collapse: collapse;
    }
    .report-data-table th {
        background: #f8f9fa;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
    }
    .report-data-table td {
        padding: 12px;
        border-bottom: 1px solid #e9ecef;
    }
    .accordion-toggle {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .accordion-toggle:hover {
        background-color: #f8f9fa;
    }
    .hiddenRow {
        padding: 0 !important;
    }
    .hiddenRow > td {
        padding: 0;
        margin: 0;
        border: none;
    }
    .hidden-row-grid {
        display: grid;
        grid-template-columns: auto auto;
        background: #f8f9fa;
        padding: 15px;
        gap: 10px;
        border-top: 1px solid #dee2e6;
    }
    .hidden-row-grid > span {
        word-break: break-word;
        padding: 5px;
    }
    .hidden-row-grid > span:nth-child(odd) {
        font-weight: 600;
        color: #495057;
    }
    .additional-block {
        background: #f8f9fa;
        padding: 15px;
        margin: 10px 0;
        border-left: 4px solid  #fd7e14;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .additional-block:hover {
        background: #e9ecef;
    }
    .additional-block.danger span {
        color:  #fd7e14;
        font-weight: 600;
    }
    .danger-additional-hidden {
        display: none;
    }
    .danger-block-additional {
        margin-top: 10px;
        text-align: center;
        cursor: pointer;
    }
    .danger-block-arrow {
        color: #0d6efd;
        font-weight: 600;
    }
    .accordion-danger {
        padding: 15px;
        background: #f8f9fa;
        border-left: 4px solid  #fd7e14;
        margin-bottom: 15px;
    }
    .left-side {
        padding: 10px;
    }
    .left-side .header {
        font-weight: 600;
        color: #495057;
        margin-bottom: 5px;
        font-size: 14px;
    }
    .left-side .text {
        color: #333;
        font-size: 14px;
        word-break: break-word;
    }
    .totop {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: #0d6efd;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.3s;
        z-index: 1000;
    }
    .totop.show {
        opacity: 1;
    }
    .totop svg {
        width: 30px;
        height: 30px;
        stroke: white;
    }
    .totop:hover {
        background: #0b5ed7;
    }
    h3.order {
        font-size: 20px;
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
    }
    @media (max-width: 768px) {
        .container.order {
            padding: 0 10px;
        }
        .row.family .col {
            font-size: 20px;
        }
        .report-data-table {
            font-size: 14px;
        }
        .report-data-table td {
            padding: 8px;
        }
        .hidden-row-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(window).on('load', function() {
        // Подсветка строк при наведении
        $('.accordion-toggle').each(function(index) {
            if (index % 2 === 0) {
                $(this).css('background', '#f8f9fa');
            }

            $(this).hover(
                function() {
                    $(this).css('background', '#e9ecef');
                },
                function() {
                    if (!$(this).hasClass('highlight')) {
                        $(this).css('background', index % 2 === 0 ? '#f8f9fa' : '');
                    }
                }
            );

            $(this).on('click', function() {
                $(this).toggleClass('highlight');
                if ($(this).hasClass('highlight')) {
                    $(this).css('background', '#dee2e6');
                } else {
                    $(this).css('background', index % 2 === 0 ? '#f8f9fa' : '');
                }
            });
        });

        // Кнопка "Показать все блоки" для скоринга негатива
        $(".danger-block-additional").click(function() {
            $(".danger-additional-hidden").fadeToggle();
            let buttonText = $(".danger-block-arrow").text();
            $(".danger-block-arrow").text(buttonText === "Показать все блоки" ? "Скрыть блоки" : "Показать все блоки");
        });

        // Кнопка "Наверх"
        $(window).scroll(function() {
            if ($(this).scrollTop() > 100) {
                $('.totop').addClass('show');
            } else {
                $('.totop').removeClass('show');
            }
        });

        $('.totop').click(function() {
            $('html, body').animate({scrollTop: 0}, 300);
        });
    });
</script>
@endpush