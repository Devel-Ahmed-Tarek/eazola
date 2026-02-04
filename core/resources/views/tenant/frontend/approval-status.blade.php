<!DOCTYPE html>
<html lang="{{ \App\Facades\GlobalLanguage::user_lang_slug() }}" dir="{{ \App\Facades\GlobalLanguage::user_lang_dir() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if($approval_status == 'pending')
            {{__('Website Under Review')}}
        @else
            {{__('Website Status')}}
        @endif
        - {{$tenant->id}}
    </title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --success-color: #10b981;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .status-container {
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        
        .status-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 50px 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }
        
        .status-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 50px;
        }
        
        .status-icon.pending {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            color: var(--warning-color);
        }
        
        .status-icon.rejected {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: var(--danger-color);
        }
        
        .status-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 15px;
        }
        
        .status-message {
            color: var(--text-muted);
            font-size: 16px;
            line-height: 1.7;
            margin-bottom: 25px;
        }
        
        .tenant-domain {
            background: #f1f5f9;
            padding: 12px 20px;
            border-radius: 10px;
            font-family: monospace;
            font-size: 14px;
            color: var(--text-dark);
            margin-bottom: 25px;
            display: inline-block;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 30px;
        }
        
        .status-badge.pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-badge.rejected {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .rejection-reason {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: right;
        }
        
        .rejection-reason h4 {
            color: #991b1b;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .rejection-reason p {
            color: #7f1d1d;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .info-box p {
            color: #1e40af;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .info-box i {
            font-size: 20px;
        }
        
        .contact-info {
            color: var(--text-muted);
            font-size: 14px;
        }
        
        .contact-info a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .contact-info a:hover {
            text-decoration: underline;
        }
        
        .refresh-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary-color);
            color: #fff;
            padding: 12px 25px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        
        .refresh-btn:hover {
            background: #4338ca;
            transform: translateY(-2px);
        }
        
        .animation-pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        
        /* RTL Support */
        [dir="rtl"] .rejection-reason {
            text-align: right;
        }
        
        [dir="ltr"] .rejection-reason {
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="status-container">
        <div class="status-card">
            @if($approval_status == 'pending')
                {{-- Pending Status --}}
                <div class="status-icon pending animation-pulse">
                    <i class="las la-clock"></i>
                </div>
                
                <h1 class="status-title">{{__('Website Under Review')}}</h1>
                
                <div class="tenant-domain">
                    {{$tenant->id}}.{{env('CENTRAL_DOMAIN')}}
                </div>
                
                <span class="status-badge pending">
                    <i class="las la-hourglass-half"></i>
                    {{__('Pending Approval')}}
                </span>
                
                <p class="status-message">
                    {{__('Thank you for registering! Your website is currently being reviewed by our team. You will receive an email notification once your website is approved.')}}
                </p>
                
                <div class="info-box">
                    <p>
                        <i class="las la-info-circle"></i>
                        {{__('This process usually takes 24-48 hours.')}}
                    </p>
                </div>
                
            @else
                {{-- Rejected Status --}}
                <div class="status-icon rejected">
                    <i class="las la-times-circle"></i>
                </div>
                
                <h1 class="status-title">{{__('Registration Not Approved')}}</h1>
                
                <div class="tenant-domain">
                    {{$tenant->id}}.{{env('CENTRAL_DOMAIN')}}
                </div>
                
                <span class="status-badge rejected">
                    <i class="las la-ban"></i>
                    {{__('Not Approved')}}
                </span>
                
                @if($approval_note)
                <div class="rejection-reason">
                    <h4><i class="las la-exclamation-triangle"></i> {{__('Reason')}}</h4>
                    <p>{{$approval_note}}</p>
                </div>
                @endif
                
                <p class="status-message">
                    {{__('Unfortunately, your website registration could not be approved at this time. Please contact our support team for more information or to resolve any issues.')}}
                </p>
            @endif
            
            <div class="contact-info">
                <p>
                    {{__('Need help?')}} 
                    <a href="mailto:{{get_static_option_central('site_global_email')}}">
                        {{__('Contact Support')}}
                    </a>
                </p>
            </div>
            
            <a href="{{url()->current()}}" class="refresh-btn">
                <i class="las la-sync"></i>
                {{__('Check Status Again')}}
            </a>
        </div>
    </div>
</body>
</html>
