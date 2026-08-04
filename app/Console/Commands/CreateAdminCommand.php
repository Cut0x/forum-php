<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Username;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

#[Signature('app:create-admin')]
#[Description('Crée ou promeut un compte administrateur')]
class CreateAdminCommand extends Command
{
    public function handle(): int
    {
        $email = $this->ask('Email de l\'administrateur');

        $validator = Validator::make(['email' => $email], ['email' => ['required', 'email']]);
        if ($validator->fails()) {
            $this->error('Email invalide.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user) {
            $user->update(['role' => User::ROLE_ADMIN]);
            $this->info("Le compte {$user->username} ({$email}) est maintenant administrateur.");

            return self::SUCCESS;
        }

        $name = $this->ask('Nom affiché');
        $password = $this->secret('Mot de passe (8 caractères minimum)');

        $validator = Validator::make(
            ['name' => $name, 'password' => $password],
            ['name' => ['required', 'string', 'min:2'], 'password' => ['required', 'string', 'min:8']]
        );
        if ($validator->fails()) {
            $this->error(implode(' ', $validator->errors()->all()));

            return self::FAILURE;
        }

        $user = User::query()->create([
            'name' => $name,
            'username' => Username::unique($name),
            'email' => $email,
            'password' => Hash::make($password),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $this->info("Compte administrateur créé : @{$user->username} ({$email}).");

        return self::SUCCESS;
    }
}
