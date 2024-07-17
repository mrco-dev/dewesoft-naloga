@echo off

if not exist ".env" (
    copy .env.example .env
    echo ".env file created from .env.example. Please update it with your environment variables."
) else (
    echo ".env file already exists."
)

echo Running composer install...
composer install && (
    echo Composer install completed successfully.
    echo Generating application key...
    php artisan key:generate && (
        echo Key generation completed successfully.
        echo Running migrations...
        php artisan migrate && (
            echo Migrations completed successfully.
            echo Installing npm dependencies...
            npm install && (
                echo NPM install completed successfully.
                echo Running npm development build...
                npm run dev && (
                    echo NPM development build completed successfully.
                    echo "Build complete. Your application should be ready to use."
                )
            )
        )
    )
) || (
    echo An error occurred. Please check the error messages above.
)

pause
