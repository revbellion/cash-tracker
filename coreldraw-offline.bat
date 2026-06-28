@echo off
title CorelDRAW Offline Launcher
color 0A
echo.
echo  ╔══════════════════════════════════════════╗
echo  ║   CORELDRAW OFFLINE LAUNCHER             ║
echo  ║   Block telemetry saat startup           ║
echo  ╚══════════════════════════════════════════╝
echo.

:: ============================================
:: STEP 1: Matikan internet
:: ============================================
echo [1/4] Mematikan internet sementara...
netsh advfirewall set allprofiles state off >nul 2>&1
if %errorlevel%==0 (
    echo       ✓ Firewall dimatikan
) else (
    echo       ! Coba method alternatif...
    powershell -Command "Disable-NetAdapter -Name (Get-NetAdapter | Where-Object {$_.Status -eq 'Up' -and $_.ConnectorPresent -eq $true} | Select-Object -First 1).Name -Confirm:$false" >nul 2>&1
)
echo.

:: ============================================
:: STEP 2: Jalankan CorelDRAW
:: ============================================
echo [2/4] Membuka CorelDRAW...

:: Cari CorelDRAW.exe di beberapa lokasi
set "CORELDRAW="

if exist "C:\Program Files\Corel\CorelDRAW Graphics Suite 2025\Programs64\CorelDRAW.exe" (
    set "CORELDRAW=C:\Program Files\Corel\CorelDRAW Graphics Suite 2025\Programs64\CorelDRAW.exe"
) else if exist "C:\Program Files\Corel\CorelDRAW Graphics Suite 2025\Programs\CorelDRAW.exe" (
    set "CORELDRAW=C:\Program Files\Corel\CorelDRAW Graphics Suite 2025\Programs\CorelDRAW.exe"
) else if exist "C:\Program Files\Corel\CorelDRAW Graphics Suite 2024\Programs64\CorelDRAW.exe" (
    set "CORELDRAW=C:\Program Files\Corel\CorelDRAW Graphics Suite 2024\Programs64\CorelDRAW.exe"
) else if exist "C:\Program Files\Corel\CorelDRAW Graphics Suite 2024\Programs\CorelDRAW.exe" (
    set "CORELDRAW=C:\Program Files\Corel\CorelDRAW Graphics Suite 2024\Programs\CorelDRAW.exe"
) else if exist "C:\Program Files\Corel\CorelDRAW Graphics Suite 2023\Programs64\CorelDRAW.exe" (
    set "CORELDRAW=C:\Program Files\Corel\CorelDRAW Graphics Suite 2023\Programs64\CorelDRAW.exe"
) else if exist "C:\Program Files\Corel\CorelDRAW Graphics Suite 2022\Programs64\CorelDRAW.exe" (
    set "CORELDRAW=C:\Program Files\Corel\CorelDRAW Graphics Suite 2022\Programs64\CorelDRAW.exe"
)

if "%CORELDRAW%"=="" (
    echo       ✗ CorelDRAW tidak ditemukan!
    echo       Coba jalankan manual, lalu tekan Enter
    echo       untuk mengaktifkan internet kembali.
    pause >nul
    goto :RECONNECT
)

echo       ✓ Ditemukan: %CORELDRAW%
start "" "%CORELDRAW%"
echo.

:: ============================================
:: STEP 3: Tunggu CorelDRAW load
:: ============================================
echo [3/4] Menunggu CorelDRAW selesai loading...
echo       (tekan ESC untuk skip & langsung hidupkan internet)
echo.

set /a count=0
:WAIT_LOOP
timeout /t 1 /nobreak >nul
set /a count+=1

:: Cek apakah CorelDRAW.exe sudah jalan
tasklist /fi "imagename eq CorelDRAW.exe" 2>nul | find /i "CorelDRAW.exe" >nul
if %errorlevel%==0 (
    echo       ✓ CorelDRAW sudah jalan! (%count% detik)
    goto :RECONNECT
)

if %count% geq 30 (
    echo       ! Timeout 30 detik - mengaktifkan internet
    goto :RECONNECT
)

:: Cek ESC key
powershell -Command "if ([Console]::KeyAvailable) { $key = [Console]::ReadKey($true); if ($key.Key -eq 'Escape') { exit 0 } else { exit 1 } } else { exit 1 }" >nul 2>&1
if %errorlevel%==0 (
    echo       ! Skip - mengaktifkan internet
    goto :RECONNECT
)

goto :WAIT_LOOP

:: ============================================
:: STEP 4: Hidupkan internet kembali
:: ============================================
:RECONNECT
echo.
echo [4/4] Mengaktifkan internet kembali...
netsh advfirewall set allprofiles state on >nul 2>&1
if %errorlevel%==0 (
    echo       ✓ Firewall diaktifkan kembali
) else (
    powershell -Command "Enable-NetAdapter -Name (Get-NetAdapter | Where-Object {$_.Status -eq 'Up' -and $_.ConnectorPresent -eq $true} | Select-Object -First 1).Name -Confirm:$false" >nul 2>&1
)
echo.
echo  ╔══════════════════════════════════════════╗
echo  ║   SELESAI!                               ║
echo  ║   CorelDRAW sudah jalan tanpa internet.  ║
echo  ║   Internet aktif kembali.                ║
echo  ╚══════════════════════════════════════════╝
echo.
echo  Tips: Jalankan script ini setiap kali mau buka CorelDRAW.
echo.
pause
