$repo = app(\Laravel\Passport\ClientRepository::class);
$personal = $repo->createPersonalAccessClient(null, 'CamDirectory Personal Access', 'http://localhost');
echo 'PERSONAL_ID=' . $personal->id . "\n";
echo 'PERSONAL_SECRET=' . $personal->secret . "\n";
