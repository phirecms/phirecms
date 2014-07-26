@echo off
REM
REM Phire CMS 2.0 BASH CLI script
REM

SETLOCAL ENABLEDELAYEDEXPANSION

SET SCRIPT_DIR=%~dp0
SET TAR=
SET ZIP=
SET PH_CLI_ROOT=..\..
SET PH_APP_PATH=
SET DB_CLI=
SET DB_CLI_DUMP=
SET DB_DUMP_NAME=
SET DB_INTERFACE=
SET DB_TYPE=
SET DB_NAME=
SET DB_USER=
SET DB_PASS=
SET DB_HOST=
SET MYSQL=false
SET PGSQL=false
SET SQLITE=false

SET HOUR=%TIME:~0,2%
SET TIMESTAMP9=%DATE:~-4%-%DATE:~4,2%-%DATE:~7,2%
SET TIMESTAMP24=%DATE:~-4%-%DATE:~4,2%-%DATE:~7,2%

if "%HOUR:~0,1%" == " " (SET TIMESTAMP=!TIMESTAMP9!) else (SET TIMESTAMP=!TIMESTAMP24!)

FOR /f "delims=" %%i IN ('where tar') DO SET TAR=%%i
FOR /f "delims=" %%i IN ('where zip') DO SET ZIP=%%i

FOR /f "delims=" %%a in ('findstr "APP_PATH" !PH_CLI_ROOT!\config.php') DO SET PH_APP_PATH=%%a
FOR /f "delims=" %%a in ('findstr "DB_INTERFACE" !PH_CLI_ROOT!\config.php') DO SET DB_INTERFACE=%%a
FOR /f "delims=" %%a in ('findstr "DB_TYPE" !PH_CLI_ROOT!\config.php') DO SET DB_TYPE=%%a
FOR /f "delims=" %%a in ('findstr "DB_NAME" !PH_CLI_ROOT!\config.php') DO SET DB_NAME=%%a
FOR /f "delims=" %%a in ('findstr "DB_USER" !PH_CLI_ROOT!\config.php') DO SET DB_USER=%%a
FOR /f "delims=" %%a in ('findstr "DB_PASS" !PH_CLI_ROOT!\config.php') DO SET DB_PASS=%%a
FOR /f "delims=" %%a in ('findstr "DB_HOST" !PH_CLI_ROOT!\config.php') DO SET DB_HOST=%%a

SET PH_APP_PATH=!PH_APP_PATH:~21,-3!
SET DB_INTERFACE=!DB_INTERFACE:~24,-3!
SET DB_TYPE=!DB_TYPE:~19,-3!
SET DB_NAME=!DB_NAME:~19,-3!
SET DB_USER=!DB_USER:~19,-3!
SET DB_PASS=!DB_PASS:~19,-3!
SET DB_HOST=!DB_HOST:~19,-3!

IF "!DB_INTERFACE!" == "Mysqli" SET MYSQL=true
IF "!DB_TYPE!" == "mysql" SET MYSQL=true

IF "!DB_INTERFACE!" == "Pgsql" SET PGSQL=true
IF "!DB_TYPE!" == "pgsql" SET PGSQL=true

IF "!DB_INTERFACE!" == "Sqlite" SET SQLITE=true
IF "!DB_TYPE!" == "sqlite" SET SQLITE=true

IF "!MYSQL!" == "true" (
    FOR /f "delims=" %%a in ('where mysql') DO SET DB_CLI=%%a
    FOR /f "delims=" %%a in ('where mysqldump') DO SET DB_CLI_DUMP=%%a
    SET DB_DUMP_NAME=!DB_NAME!_!TIMESTAMP!.mysql.sql
)

IF "!PGSQL!" == "true" (
    FOR /f "delims=" %%a in ('where psql') DO SET DB_CLI=%%a
    FOR /f "delims=" %%a in ('where pg_dump') DO SET DB_CLI_DUMP=%%a
    SET DB_DUMP_NAME=!DB_NAME!_!TIMESTAMP!.pgsql.sql
)

IF "!SQLITE!" == "true" (
    FOR /f "delims=" %%a in ('where sqlite3') DO SET DB_CLI=%%a
    FOR /f "delims=" %%a in ('where sqlite3') DO SET DB_CLI_DUMP=%%a
    SET DB_NAME=!DB_NAME:~10!
    SET DB_DUMP_NAME=phirecms_!TIMESTAMP!.sqlite.sql
)

IF "%1" == "sql" (
    IF NOT "!DB_INTERFACE!" == "" (
        IF "!MYSQL!" == "true" (
            IF "%2" == "cli" (
                IF NOT "!DB_CLI!" == "" (
                    "!DB_CLI!" --database=!DB_NAME! --user=!DB_USER! --password=!DB_PASS! --host=!DB_HOST!
                ) ELSE (
                    echo.
                    echo   That database CLI client was not found.
                    echo.
                )
            )
            IF "%2" == "dump" (
                IF NOT "!DB_CLI_DUMP!" == "" (
                    echo.
                    echo   Dumping Phire CMS 2 Database...
                    "!DB_CLI_DUMP!" --user=!DB_USER! --password=!DB_PASS! --host=!DB_HOST! !DB_NAME! > !DB_NAME!_!TIMESTAMP!.mysql.sql
                    echo   Done!
                    echo.
                ) ELSE (
                    echo.
                    echo   That database CLI dump client was not found.
                    echo.
                )
            )
        )

        IF "!PGSQL!" == "true" (
            IF "%2" == "cli" (
                IF NOT "!DB_CLI!" == "" (
                    "!DB_CLI!" --dbname=!DB_NAME! --username=!DB_USER!
                ) ELSE (
                    echo.
                    echo   That database CLI client was not found.
                    echo.
                )
            )
            IF "%2" == "dump" (
                IF NOT "!DB_CLI_DUMP!" == "" (
                    SET TIMESTAMP=%DATE:~-4%-%DATE:~4,2%-%DATE:~7,2%_%TIME:~0,2%-%TIME:~3,2%
                    echo.
                    echo   Dumping Phire CMS 2 Database...
                    "!DB_CLI_DUMP!" --username=!DB_USER! !DB_NAME!> !DB_NAME!_!TIMESTAMP!.pgsql.sql
                    echo   Done!
                    echo.
                ) ELSE (
                    echo.
                    echo   That database CLI dump client was not found.
                    echo.
                )
            )
        )

        IF "!SQLITE!" == "true" (
            IF "%2" == "cli" (
                IF NOT "!DB_CLI!" == "" (
                    "!DB_CLI!" "!PH_CLI_ROOT!!DB_NAME!"
                ) ELSE (
                    echo.
                    echo   That database CLI client was not found.
                    echo.
                )
            )
            IF "%2" == "dump" (
                IF NOT "!DB_CLI_DUMP!" == "" (
                    echo.
                    echo   Dumping Phire CMS 2 Database...
                    "!DB_CLI_DUMP!" "!PH_CLI_ROOT!!DB_NAME!" .dump > phirecms_!TIMESTAMP!.sqlite.sql"
                    echo   Done!
                    echo.
                ) ELSE (
                    echo.
                    echo   That database CLI dump client was not found.
                    echo.
                )
            )
        )
    ) ELSE (
        echo.
        echo Phire CMS 2 CLI
        echo ===============
        echo.
        echo   Phire CMS 2 does not appear to be installed. Please check the config file or install the application.
        echo.
    )
)

IF "%1" == "archive" (
    echo.
    echo Phire CMS 2 CLI
    echo ===============
    echo.
    IF NOT "!DB_CLI_DUMP!" == "" (
        echo   Archiving the Phire CMS 2 installation...
        IF "!MYSQL!" == "true" (
            "!DB_CLI_DUMP!" --user=!DB_USER! --password=!DB_PASS! --host=!DB_HOST! !DB_NAME! > !DB_NAME!_!TIMESTAMP!.mysql.sql
        )
        IF "!PGSQL!" == "true" (
            "!DB_CLI_DUMP!" --username=!DB_USER! !DB_NAME!> !DB_NAME!_!TIMESTAMP!.pgsql.sql
        )
        IF "!SQLITE!" == "true" (
            "!DB_CLI_DUMP!" "!PH_CLI_ROOT!!DB_NAME!" .dump > phirecms_!TIMESTAMP!.sqlite.sql"
        )
        IF NOT "!TAR!" == "" (
            "!TAR!" -cvzpf phirecms_!TIMESTAMP!.tar.gz !PH_CLI_ROOT!/.htaccess !PH_CLI_ROOT!/*
            echo   Done!
            echo.
        ) ELSE (
            IF NOT "!ZIP!" == "" (
                "!ZIP!" -r -v phirecms_!TIMESTAMP!.zip !PH_CLI_ROOT!\.htaccess !PH_CLI_ROOT!\*
                echo   Done!
                echo.
            ) ELSE (
                echo.
                echo   Neither the TAR or ZIP utilities were found.
                echo.
            )
        )
        rm !DB_DUMP_NAME!
    ) ELSE (
        echo.
        echo   That database CLI dump client was not found.
        echo.
    )
)

IF NOT "%1" == "archive" (
    IF NOT "%1" == "sql" (
        php %SCRIPT_DIR%phire.php %*
        IF "%1" == "install" (
            php %SCRIPT_DIR%phire.php post
        )

        IF EXIST !PH_CLI_ROOT!\phire-cms-new (
            IF EXIST !PH_CLI_ROOT!\phire-cms-new\vendor\Phire\data\update.php (
                php "!PH_CLI_ROOT!\phire-cms-new\vendor\Phire\data\update.php"
                echo   For the Windows OS, you will have to manually rename the new system folder '/phire-cms-new'
                echo   to the correct application path, due to file and folder permission restrictions.
                echo.
            )
        )
    )
)

ENDLOCAL
