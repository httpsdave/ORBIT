<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation Sheet</title>
    <style>
        /* Set A4 paper size for print */
        @page {
            size: A4;
            margin-top: 0.5cm;
            margin-bottom: 1.0cm;
            margin-left: 2.54cm;
            margin-right: 2.54cm;
        }

        body {
            font-family: 'Calibri', sans-serif;
            font-size: 11pt;
            line-height: 1.2;
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
        }
        
        .university-name {
            max-width: 45%;
            height: auto;
            margin: 4px 0;
            display: inline-block;
        }
        
        .logo {
            position: absolute;
            top: -0.5cm;
            left: -2cm;
            width: 250px;
            height: auto;
        }
        
        .title {
            font-weight: bold;
            font-size: 12pt;
            margin: 20px 0;
        }
        
        .form-section {
            margin-bottom: 15px;
        }
        
        .form-row {
            margin-bottom: 8px;
        }
        
        .underline {
            border-bottom: 1px solid black;
            display: inline-block;
            min-width: 300px;
            padding-bottom: 2px;
        }
        
        .rating-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .rating-table th,
        .rating-table td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
            vertical-align: middle;
        }
        
        .rating-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .rating-table .question-col {
            text-align: left;
            width: 60%;
        }
        
        .rating-scale {
            margin: 15px 0;
        }
        
        .rating-scale div {
            margin-bottom: 3px;
        }
        
        .comments-section {
            margin-top: 20px;
        }
        
        .comments-lines {
            border-bottom: 1px solid black;
            height: 100px;
            margin-top: 10px;
        }
        
        .bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <img src="{{ public_path('images/lspu-logo.png') }}" alt="LSPU Logo" class="logo">
        <span style="font-size:10pt;">Republic of the Philippines</span><br>
        <img src="{{ public_path('images/lspu-name.png') }}" alt="Laguna State Polytechnic University" class="university-name"><br>
        <span style="font-size:10pt;">Province of Laguna</span><br>
        <br>
    </div>
    <div class="title" style="text-align: center; font-size: 16px; font-weight: bold; margin: 20px 0 30px 0;">Evaluation Sheet for all Programs/Activities</div>
    
    <!-- Form Fields -->
    <div class="form-section">
        <div class="form-row" style="margin-bottom: 7px;">
            <span class="bold">Title of the Activity:</span> <span style="border-bottom: 1px solid black; display: inline-block; width: calc(100% - 160px); margin-left: 8px;"></span>
        </div>
        <div class="form-row" style="margin-bottom: 7px;">
            <span class="bold">Venue:</span> <span style="border-bottom: 1px solid black; display: inline-block; width: calc(100% - 70px); margin-left: 8px;"></span>
        </div>
        <div class="form-row" style="margin-bottom: 7px;">
            <span class="bold">Date:</span> <span style="border-bottom: 1px solid black; display: inline-block; width: calc(100% - 55px); margin-left: 8px;"></span>
        </div>
        <div class="form-row" style="margin-bottom: 7px;">
            <span class="bold">Time:</span> <span style="border-bottom: 1px solid black; display: inline-block; width: calc(100% - 55px); margin-left: 8px;"></span>
        </div>
    </div>
    
    <div style="margin: 20px 0;">
        <span style="font-size:10pt;">Direction: Please put a check (√) at the following statements with the corresponding rating scale.</span>
    </div>
    
    <!-- Rating Scale -->
    <div class="rating-scale">
        <div style="font-weight:normal;">Rating Scale:</div>
        <div style="margin-left: 120px;">
            <div style="display: flex; justify-content: flex-start;">
                <span style="min-width: 120px; display: inline-block;">Excellent</span>
                <span style="min-width: 35px; display: inline-block; text-align: right;">5</span>
            </div>
            <div style="display: flex; justify-content: flex-start;">
                <span style="min-width: 120px; display: inline-block;">Very Satisfactory</span>
                <span style="min-width: 35px; display: inline-block; text-align: right;">4</span>
            </div>
            <div style="display: flex; justify-content: flex-start;">
                <span style="min-width: 120px; display: inline-block;">Satisfactory</span>
                <span style="min-width: 35px; display: inline-block; text-align: right;">3</span>
            </div>
            <div style="display: flex; justify-content: flex-start;">
                <span style="min-width: 120px; display: inline-block;">Fairly Satisfactory</span>
                <span style="min-width: 35px; display: inline-block; text-align: right;">2</span>
            </div>
            <div style="display: flex; justify-content: flex-start;">
                <span style="min-width: 120px; display: inline-block;">Not Satisfactory</span>
                <span style="min-width: 35px; display: inline-block; text-align: right;">1</span>
            </div>
        </div>
    </div>
    
    <!-- Evaluation Table -->
    <table class="rating-table">
        <thead>
            <tr>
                <th style="width: 5%;">&nbsp;</th>
                <th class="question-col">&nbsp;</th>
                <th style="width: 7%;">5</th>
                <th style="width: 7%;">4</th>
                <th style="width: 7%;">3</th>
                <th style="width: 7%;">2</th>
                <th style="width: 7%;">1</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="bold">1.</td>
                <td class="question-col">The activity is well planned and organized.</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="bold">2.</td>
                <td class="question-col">The time allocation for various activity adequate.</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="bold">3.</td>
                <td class="question-col">There is a smooth interpersonal relationship among the participants.</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="bold">4.</td>
                <td class="question-col">The trust and unity among the participants are prevalent.</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="bold">5.</td>
                <td class="question-col">The objectives of those activity are attained.</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="bold">6.</td>
                <td class="question-col">The session/activities are congruent with objectives.</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="bold">7.</td>
                <td class="question-col">The venue is conductive for the activities.</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="bold">8.</td>
                <td class="question-col">The activity venue is clean, orderly and properly ventilated.</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="bold">9.</td>
                <td class="question-col">The resource speakers/facilitator/s are competent.</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="bold">10.</td>
                <td class="question-col">The resource speakers are orderly in preparation.</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="bold">11.</td>
                <td class="question-col">The resource speaker has successfully met the expectations and needs of the participants.</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="bold">12.</td>
                <td class="question-col">The speaker/s manifest rapport with the participants.</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="bold">13.</td>
                <td class="question-col">The various activity/ies is/are interesting and enjoyable.</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="bold">14.</td>
                <td class="question-col">The officers are professional in dealing with the participants.</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="bold">15.</td>
                <td class="question-col">The officers and other participants are prompt and enthusiastic enough in attending the training.</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        </tbody>
    </table>
    
    <!-- Comments Section -->
    <div class="comments-section">
        <div class="bold">Comments & Suggestions:</div>
        <div class="comments-lines"></div>
    </div>
    
</body>
</html>
