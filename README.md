# Media Package

Media file and folder management package for Laravel: upload, organize into a folder tree, and serve/download files, with a REST API for admin UIs.

## Függőségek

- `istvanmolitor/admin` (composer `require`) – a `permission:media` middleware és az admin API konvenciók (DataTable/HasAdminFilters) miatt.

## Telepítés

1) Telepítés Composerrel

Ha önálló csomagként használod:

```
composer require istvanmolitor/media
```

Monorepo/fejlesztői környezetben (path repository-val) add hozzá a gyökér `composer.json`-hoz:

```json
{
    "require": {
        "istvanmolitor/media": "*@dev"
    },
    "repositories": [
        {
            "type": "path",
            "url": "packages/media"
        }
    ]
}
```

2) Autodiscovery

A csomag Laravel Package Discovery-val regisztrálja a `Molitor\Media\Providers\MediaServiceProvider`-t, ami betölti a migrációkat (`src/Database/Migrations`) és a `web`/`api` route-okat.

3) Migrációk futtatása

```bash
php artisan migrate
```

## Features

- Media file management (feltöltés, mozgatás, törlés)
- Folder structure organization (fa struktúra, mozgatás)
- API endpoints for file operations

## Route-ok

### Publikus (auth nélkül)

- `GET downloads/{id}/{filename}` (`media.files.download`) – fájl letöltése/kiszolgálása

### Admin API (`/api/media`, `auth:sanctum` + `permission:media`)

- `GET api/media/folders/tree` – mappafa lekérdezése
- `PATCH api/media/folders/{id}/move` – mappa mozgatása
- `Route::resource('folders', MediaFolderApiController::class)`
- `PATCH api/media/files/{id}/move` – fájl mozgatása másik mappába
- `Route::resource('files', MediaFileApiController::class)`

## Repository-k

A provider a következő interfészeket köti a Laravel konténerbe:

- `Molitor\Media\Repositories\MediaFileRepositoryInterface` → `MediaFileRepository`
- `Molitor\Media\Repositories\MediaFolderRepositoryInterface` → `MediaFolderRepository`

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

## Licenc

MIT
