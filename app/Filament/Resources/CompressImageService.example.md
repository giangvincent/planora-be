# Example Filament Form Usage with CompressImageService

## File Upload Example

```php
use App\Filament\Resources\CompressImageService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

// In your Filament Resource form() method:

Section::make('Media')
    ->schema([
        FileUpload::make('cover_image_path')
            ->label('Cover Image')
            ->disk('r2')                     // still used for URLs / preview
            ->directory('posts/covers')      // logical target directory
            ->visibility('public')
            ->image()
            ->imageEditor()
            ->maxSize(5120)                  // 5 MB before compression
            ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                return CompressImageService::compress('posts/covers/', $file);
            })
    ]),

Section::make('Content')
    ->columnSpanFull()
    ->schema([
        Textarea::make('excerpt')
            ->rows(3)
            ->label('Excerpt'),

        RichEditor::make('body')
            ->label('Body')
            ->fileAttachmentsDisk('r2')
            ->fileAttachmentsDirectory('posts/attachments')
            ->fileAttachmentsVisibility('public')
            ->columnSpanFull()
            ->saveUploadedFileAttachmentUsing(function (TemporaryUploadedFile $file): string {
                return CompressImageService::compress('posts/attachments/', $file);
            }),
    ]),
```

## How It Works

1. **Tinify Compression**: Images are compressed using TinyPNG/Tinify API and converted to WebP format
2. **Cloudflare R2 Upload**: Compressed images are uploaded to Cloudflare R2 storage
3. **Automatic Cleanup**: Temporary files are automatically deleted after upload
4. **Return Path**: Returns the R2 path (e.g., `posts/covers/uuid.webp`) to store in your database

## Setup Requirements

1. Install Tinify PHP package:
   ```bash
   composer require tinify/tinify
   ```

2. Add to your `.env`:
   ```env
   # Cloudflare R2
   R2_ACCESS_KEY_ID=your_access_key
   R2_SECRET_ACCESS_KEY=your_secret_key
   R2_BUCKET=your_bucket_name
   R2_ACCOUNT_ID=your_account_id
   R2_PUBLIC_DOMAIN=https://your-domain.com

   # Tinify
   TINIFY_KEY=your_tinify_api_key
   ```

3. The service will:
   - Create a temporary directory at `storage/app/tmp/tinify`
   - Compress and convert images to WebP
   - Upload to R2 with public visibility
   - Clean up temporary files

## Notes

- Images are automatically converted to WebP format for optimal web performance
- Maximum file size before compression: 5 MB (configurable)
- The `disk('r2')` in FileUpload is used for preview/URL generation
- The actual upload is handled by `saveUploadedFileUsing()` callback
