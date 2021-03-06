<?php

/*
 * This file is part of jwt-auth.
 *
 * @author Sean Tymon <tymon148@gmail.com>
 * @copyright Copyright (c) Sean Tymon
 * @link https://github.com/tymondesigns/jwt-auth
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tymon\JWTAuth\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;

class JWTGenerateSecretCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'jwt:secret {show : Simply display the key instead of modifying files.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the JWTAuth secret key used to sign the tokens';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $key = $this->getRandomKey();

        if ($this->option('show')) {
            return $this->comment($key);
        }

        $path = base_path('.env');

        if (file_exists($path)) {

            // check if there is already a secret set first
            if (! Str::contains(file_get_contents($path), 'JWT_SECRET')) {
                file_put_contents($path, "\r\nJWT_SECRET=$key", FILE_APPEND);
            } else {

                // let's be sure you want to do this
                $confirmed = $this->confirm('This will invalidate all existing tokens. Are you sure you want to override the secret key?');

                if ($confirmed) {
                    file_put_contents($path, str_replace(
                        'JWT_SECRET=' . $this->laravel['config']['jwt.secret'], 'JWT_SECRET=' . $key, file_get_contents($path)
                    ));
                } else {
                    return $this->comment('Phew... No changes were made to your secret key.');
                }
            }
        }

        $this->laravel['config']['jwt.secret'] = $key;

        $this->info("jwt-auth secret [$key] set successfully.");
    }

    /**
     * Generate a random key for the JWT Auth secret.
     *
     * @return string
     */
    protected function getRandomKey()
    {
        return Str::quickRandom(32);
    }
}
