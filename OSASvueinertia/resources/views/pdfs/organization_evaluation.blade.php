<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation Sheet for all Programs/Activities</title>
    <style>
        @page {
            size: A4;
            margin-top: 0.5cm;
            margin-bottom: 1.0cm;
            margin-left: 2.54cm;
            margin-right: 2.54cm;
        }
        body {
            font-family: Calibri, Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.1;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .header {
            text-align: center;
            font-size: 11pt;
            margin: 0 0 2px 0;
            padding-top: 0.5cm;
            position: relative;
            font-family: Calibri, Arial, sans-serif;
        }
        .logo {
            position: absolute;
            top: -0.5cm;
            left: -2cm;
            width: 250px;
            height: auto;
        }
        .university-name {
            font-size: 11pt;
            font-family: Calibri, Arial, sans-serif;
            font-weight: bold;
            max-width: 60%;
            height: auto;
            margin: 4px 0;
            display: inline-block;
        }
        .form-title {
            font-size: 11pt;
            font-weight: bold;
            margin: 0;
            font-family: Calibri, Arial, sans-serif;
        }
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            margin-top: 10px;
        }
        .fields-table {
            width: 100%;
            margin-bottom: 10px;
        }
        .fields-table td {
            padding: 3px 8px;
            font-size: 11pt;
            font-family: Calibri, Arial, sans-serif;
        }
        .direction {
            margin-bottom: 4px;
            font-size: 10.5pt;
        }
        .rating-scale {
            margin-bottom: 8px;
        }
        .rating-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .rating-table th, .rating-table td {
            border: 1px solid #444;
            padding: 4px 8px;
            font-size: 11pt;
            font-family: Calibri, Arial, sans-serif;
        }
        .rating-table th {
            background: #f2f2f2;
            text-align: center;
        }
        .statement-cell {
            text-align: left;
        }
        .average-header {
            text-align: center;
            font-weight: bold;
        }
        .rating-cell {
            text-align: center !important;
            padding-right: 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/lspu-logo.png') }}" alt="LSPU Logo" class="logo">
        <p class="calibri-font" style="margin-bottom: 0;">Republic of the Philippines</p>
        <img src="{{ public_path('images/lspu-name.png') }}" alt="Laguna State Polytechnic University" class="university-name"><br>
        <p class="calibri-font" style="margin-top: 0;">Province of Laguna</p>
        <br>
        <p class="form-title" style="margin-bottom: 24px;">Evaluation Sheet for all Programs/Activities</p>
    </div>
    <div class="main-content">
        <div style="margin-bottom: 8px;">
            <span style="font-weight: bold;">Title of the Activity:</span>
            <span style="margin-left: 6px;">{{ $application->activity_title ?? '' }}</span>
        </div>
        <div style="margin-bottom: 8px;">
            <span style="font-weight: bold;">Venue:</span>
            <span style="margin-left: 6px;">{{ $application->venue ?? '' }}</span>
        </div>
        @php
            // Format date range
            $dateDisplay = '';
            if (!empty($application->date_start)) {
                $dateDisplay = \Carbon\Carbon::parse($application->date_start)->format('F j, Y');
                if (!empty($application->date_end) && $application->date_end !== $application->date_start) {
                    $dateDisplay .= ' - ' . \Carbon\Carbon::parse($application->date_end)->format('F j, Y');
                }
            } elseif (!empty($application->date)) {
                $dateDisplay = $application->date;
            }

            // Format time range
            $timeDisplay = '';
            if (!empty($application->time_start)) {
                $timeDisplay = date('g:i A', strtotime($application->time_start));
                if (!empty($application->time_end) && $application->time_end !== $application->time_start) {
                    $timeDisplay .= ' - ' . date('g:i A', strtotime($application->time_end));
                }
            } elseif (!empty($application->time)) {
                $timeDisplay = $application->time;
            }
        @endphp

        <div style="margin-bottom: 8px;">
            <span style="font-weight: bold;">Date:</span>
            <span style="margin-left: 6px;">{{ $dateDisplay }}</span>
        </div>
        <div style="margin-bottom: 24px;">
            <span style="font-weight: bold;">Time:</span>
            <span style="margin-left: 6px;">{{ $timeDisplay }}</span>
        </div>
        <div class="direction" style="margin-top: 18px; margin-bottom: 28px;">
            Direction: Please put a check (<span style="font-family: 'DejaVu Sans', sans-serif;">&#10003;</span>) at the following statements with the corresponding rating scale.
        </div>
        <div style="font-weight: normal; margin-bottom: 14px;">Rating Scale:</div>
        <div class="rating-scale" style="margin-bottom: 18px;">
            <table style="margin-bottom: 0; margin-left: 100px;">
                <tr><td>Excellent</td><td style="padding-left: 20px;">5</td></tr>
                <tr><td>Very Satisfactory</td><td style="padding-left: 20px;">4</td></tr>
                <tr><td>Satisfactory</td><td style="padding-left: 20px;">3</td></tr>
                <tr><td>Fairly Satisfactory</td><td style="padding-left: 20px;">2</td></tr>
                <tr><td>Not Satisfactory</td><td style="padding-left: 20px;">1</td></tr>
            </table>
        </div>
        <table class="rating-table">
            <thead>
                <tr>
                    <th class="average-header" style="text-align: left;">&nbsp;</th>
                    <th class="average-header" style="width: 80px;">Average</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $statements = [
                        'The activity is well planned and organized.',
                        'The time allocation for various activity adequate.',
                        'There is a smooth interpersonal relationship among the participants.',
                        'The trust and unity among the participants are prevalent.',
                        'The objectives of those activity are attained.',
                        'The session/activities are congruent with objectives.',
                        'The venue is conducive for the activities.',
                        'The activity venue is clean, orderly and properly ventilated.',
                        'The resource speakers/facilitator/s are competent.',
                        'The resource speakers are orderly in preparation.',
                        'The resource speaker has successfully met the expectations and<br><span style="display:inline-block; width: 22px;"></span>needs of the participants.',
                        'The speaker/s manifest rapport with the participants.',
                        'The various activity/ies is/are interesting and enjoyable.',
                        'The officers are professional in dealing with the participants.',
                        'The officers and other participants are prompt and enthusiastic enough<br><span style="display:inline-block; width: 22px;"></span>in attending the training.'
                    ];
                    $ratings = $application->ratings ?? [];
                    $averages = $application->averages ?? null;
                @endphp
                @foreach($statements as $i => $statement)
                <tr>
                    <td class="statement-cell">
                        <span style="display:inline-block; width: 22px;">{{ ($i + 1) . '.' }}</span>
                        @if($i === 10)
                            The resource speaker has successfully met the expectations and<br>
                            <span style="display:inline-block; width: 28px;"></span>needs of the participants.
                        @elseif($i === 14)
                            The officers and other participants are prompt and enthusiastic enough<br>
                            <span style="display:inline-block; width: 28px;"></span>in attending the training.
                        @else
                            {{ $statement }}
                        @endif
                    </td>
                    @if($averages)
                        <td class="rating-cell">
                            @if(isset($averages[$i]))
                                {{ number_format($averages[$i], 1) }}
                            @else
                                &mdash;
                            @endif
                        </td>
                    @else
                        <td class="rating-cell">
                            @if(isset($ratings[$i]) && $ratings[$i] !== null && $ratings[$i] !== '')
                                {{ number_format((float)$ratings[$i], 1) }}
                            @else
                                &mdash;
                            @endif
                        </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
        @if(!empty($application->comments_suggestions))
            <div style="margin-top: 24px;">
                <strong>Comments & Suggestions:</strong>
                <ul style="margin-top: 8px; margin-bottom: 0; padding-left: 24px;">
                    @foreach(preg_split('/\r\n|\r|\n/', $application->comments_suggestions) as $line)
                        @if(trim($line) !== '')
                            <li>{{ $line }}</li>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</body>
</html> 