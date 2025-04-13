<!DOCTYPE html>
<html>
<head>
    <title>Status Update</title>
</head>
<body>
    <p>Dear {{ $applicant->jobseeker->user->name }},</p>

    <p>Your application for the project <strong>{{ $project->title }}</strong> has been <strong>{{ $applicant->jobseeker_status }}</strong>.</p>

    <p>Thank you for applying on our platform.</p>

    <p>Regards,<br>Team SkillLink</p>
</body>
</html>
