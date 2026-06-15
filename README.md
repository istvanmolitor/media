# Media Package

Media management package for file and folder handling.

## Features

- Media file management
- Folder structure organization
- API endpoints for file operations

## Seeder regisztrálása

A jogosultságok kezdeti beállításához regisztráld a seedert a `database/seeders/DatabaseSeeder.php` fájlban:

```php
use Molitor\Media\Database\Seeders\MediaSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            MediaSeeder::class,
        ]);
    }
}
```
