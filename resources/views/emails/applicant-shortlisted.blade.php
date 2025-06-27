<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .meeting-details { background-color: #e8f5e8; padding: 15px; border-left: 4px solid #4CAF50; margin: 15px 0; }
        .meeting-link { background-color: #2196F3; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0; }
        .footer { text-align: center; padding: 20px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🎉 Congratulations! You've Been Shortlisted</h2>
        </div>

        <div class="content">
            <p><strong>Dear Candidate,</strong></p>

            <p>We are pleased to inform you that you have been <strong>shortlisted</strong> for the position:</p>
            <h3 style="color: #4CAF50;">{{$project_title}}</h3>

            @if(isset($meeting_link) && $meeting_link)
                <div class="meeting-details">
                    <h4>📅 Interview Details</h4>
                    <p><strong>Date & Time:</strong> {{$start_time}} to {{$end_time}}</p>
                    <p><strong>Meeting Type:</strong> Online Video Interview</p>

                    <p><strong>How to Join:</strong></p>
                    <ol>
                        <li>Click the meeting link below at the scheduled time</li>
                        <li>Allow camera and microphone access when prompted</li>
                        <li>Ensure you have a stable internet connection</li>
                        <li>Join a few minutes early to test your setup</li>
                        @if(str_contains($meeting_link, 'meet.jit.si'))
                        <li><strong>Note:</strong> This interview uses Jitsi Meet - no account required, works in any browser</li>
                        @endif
                    </ol>

                    <div style="text-align: center;">
                        <a href="{{$meeting_link}}" class="meeting-link" target="_blank">
                            🎥 Join Interview Meeting
                        </a>
                    </div>

                    <p><em>Meeting Link: {{$meeting_link}}</em></p>
                </div>

                <p><strong>Important Notes:</strong></p>
                <ul>
                    <li>Please be punctual and join the meeting on time</li>
                    <li>Dress professionally for the video interview</li>
                    <li>Prepare your questions about the role and company</li>
                    <li>Have your resume and portfolio ready to discuss</li>
                </ul>
            @else
                <div class="meeting-details">
                    <p>📞 <strong>Next Steps:</strong></p>
                    <p>You will be contacted shortly with further details about the interview process.</p>
                </div>
            @endif

            <p>We look forward to speaking with you and learning more about your qualifications.</p>

            <p>If you have any questions or need to reschedule, please contact us immediately.</p>
        </div>

        <div class="footer">
            <p><strong>Best regards,</strong><br>
            The SkillLink Team</p>
            <p><em>Connecting talent with opportunities</em></p>
        </div>
    </div>
</body>
</html>