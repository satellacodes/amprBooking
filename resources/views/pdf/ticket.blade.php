<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>E-Ticket AMPR</title>
    <style>
        /* 1. SETUP KERTAS AGAR TIDAK KEPOTONG */
        @page {
            margin: 0px; /* Hapus margin default printer */
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 20px; /* Padding manual dari body */
            background-color: #F4E7FB; /* Background Ungu Muda */
            color: #5A4D61;
        }

        .container {
            width: 100%;
        }

        /* 2. CARD STYLE */
        .ticket-card {
            background-color: #ffffff;
            width: 100%;
            border-radius: 15px;
            overflow: hidden;
            /* Mencegah card terpotong halaman */
            page-break-inside: avoid; 
        }

        /* 3. HEADER BARU (ORANGE PEACH) */
        .header {
            background-color: #C8A8E9 ;
            color: #ffffff;
            padding: 20px; /* Diperkecil biar hemat tempat */
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: 900;
        }

        .header p {
            margin: 2px 0 0;
            font-size: 9px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* CONTENT */
        .content {
            padding: 20px 25px;
        }

        .booking-code-label {
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            color: #9D8CA6; /* Ungu Abu */
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }

        .booking-code {
            text-align: center;
            font-size: 28px; /* Diperkecil dikit */
            font-weight: bold;
            color: #FF9973;
            margin: 0 0 15px 0;
            font-family: 'Courier New', Courier, monospace;
            letter-spacing: 2px;
        }

        .qr-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .qr-box {
            display: inline-block;
            padding: 8px;
            border: 1px dashed #F6BCBA; /* Garis putus-putus Peach */
            border-radius: 10px;
        }

        .qr-img {
            width: 120px;
            height: 120px;
            display: block;
        }

        /* TABLE DETAIL */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .details-table td {
            padding: 8px 0; /* Padding diperkecil */
            vertical-align: top;
            border-bottom: 1px solid #F4E7FB;
        }

        .details-table tr:last-child td {
            border-bottom: none;
        }

        .label {
            font-size: 8px;
            color: #9D8CA6;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 2px;
        }

        .value {
            font-size: 12px;
            font-weight: bold;
            color: #5A4D61;
            line-height: 1.4; /* Jarak antar baris teks panjang */
        }

        /* FOOTER */
        .footer {
            background-color: #dbc5e1; /* Pink sangat muda */
            text-align: center;
            padding: 12px;
            font-size: 8px;
            color: #ffff;
            border-top: 1px dashed #F6BCBA;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="ticket-card">
            
            <!-- Header Orange Peach -->
            <div class="header">
                <h1>E-TICKET LAPANGAN</h1>
                <p>Apartemen Mediterania Palace</p>
            </div>

            <div class="content">
                <!-- Kode Booking -->
                <div class="booking-code-label">Kode Booking</div>
                <div class="booking-code">{{ strtoupper($booking->booking_code) }}</div>

                <!-- QR Code -->
                <div class="qr-container">
                    <div class="qr-box">
                        <img src="{{ $qrCode }}" class="qr-img" />
                    </div>
                </div>

                <!-- Detail Info -->
                <table class="details-table">
                    <tr>
                        <td width="60%">
                            <span class="label">Tanggal Main</span>
                            <span class="value">{{ \Carbon\Carbon::parse($booking->start_time)->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                        </td>
                        <td width="40%" style="text-align: right;">
                            <span class="label">Unit Apartemen</span>
                            <span class="value" style="font-size: 14px; color: #7ECFE0;">{{ $booking->unit->unit_number }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="label">Jam</span>
                            <span class="value">
                                {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - 
                                {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <span class="label">Status</span>
                            <span class="value" style="color: #FF9973;">CONFIRMED</span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <span class="label">Nama Pemain</span>
                            <!-- word-wrap memastikan nama panjang turun ke bawah -->
                            <span class="value" style="word-wrap: break-word;">
                                {{ is_array($booking->player_names) ? $booking->player_names[0] : $booking->player_names }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Footer -->
            <div class="footer">
                Tunjukkan QR Code ini kepada petugas keamanan di lobby.<br>
                Harap datang 10 menit sebelum jadwal main.
            </div>

        </div>
    </div>

</body>
</html>