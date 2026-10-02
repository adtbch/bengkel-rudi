Add-Type -AssemblyName System.Drawing

$root    = 'D:\Kerja\Portofolio bengkel\portofolio-bengkel-rudi'
$source  = Join-Path $root 'public\images\logo-bengkel-rudi.png'
$tempDir = Join-Path $env:TEMP 'icon_build'

if (Test-Path $tempDir) { Remove-Item $tempDir -Recurse -Force }
New-Item -ItemType Directory -Path $tempDir | Out-Null

$original = [System.Drawing.Image]::FromFile($source)
Write-Output "source: $($original.Width)x$($original.Height)"

# Centre-crop helper: the source is square, but stay defensive.
function New-SquareFrame([int]$side) {
    $longest = [Math]::Max($original.Width, $original.Height)
    $left = [int](($original.Width - $longest) / 2)
    $top  = [int](($original.Height - $longest) / 2)
    return @($side, $left, $top, $longest)
}

function New-ResizedBitmap([int]$side) {
    $bitmap = New-Object System.Drawing.Bitmap($side, $side, [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
    $g = [System.Drawing.Graphics]::FromImage($bitmap)
    $g.InterpolationMode = 'HighQualityBicubic'
    $g.SmoothingMode = 'HighQuality'
    $g.PixelOffsetMode = 'HighQuality'
    $g.CompositingQuality = 'HighQuality'
    $g.Clear([System.Drawing.Color]::Transparent)
    $frame = New-SquareFrame $side
    $g.DrawImage($original,
        (New-Object System.Drawing.Rectangle(0, 0, $side, $side)),
        (New-Object System.Drawing.Rectangle($frame[1], $frame[2], $frame[3], $frame[3])),
        [System.Drawing.GraphicsUnit]::Pixel)
    $g.Dispose()
    return $bitmap
}

# 1. Apple touch icons (referenced by layouts/public.blade.php).
foreach ($side in @(180, 192)) {
    $bmp = New-ResizedBitmap $side
    $out = Join-Path $root "public\images\apple-touch-icon-$side.png"
    $bmp.Save($out, [System.Drawing.Imaging.ImageFormat]::Png)
    $bmp.Dispose()
    Write-Output "apple-touch-icon-$side.png  $((Get-Item $out).Length) bytes"
}

# 2. Small logo for navbar / hero / admin chrome (displayed at 44-72 px).
foreach ($side in @(128, 256)) {
    $bmp = New-ResizedBitmap $side
    $out = Join-Path $root "public\images\logo-bengkel-rudi-$side.png"
    $bmp.Save($out, [System.Drawing.Imaging.ImageFormat]::Png)
    $bmp.Dispose()
    Write-Output "logo-bengkel-rudi-$side.png  $((Get-Item $out).Length) bytes"
}

# 3. Multi-size favicon.ico.
$icoFrames = @()
foreach ($side in @(16, 32, 48, 64, 128, 256)) {
    $bmp = New-ResizedBitmap $side
    $png = Join-Path $tempDir "f-$side.png"
    $bmp.Save($png, [System.Drawing.Imaging.ImageFormat]::Png)
    $icoFrames += ,@($side, $png, $bmp)
}

$entries = @()
$offset = 6 + (16 * $icoFrames.Count)
foreach ($frame in $icoFrames) {
    $bytes = [System.IO.File]::ReadAllBytes($frame[1])
    $entries += ,@($frame[0], $bytes, $offset)
    $offset += $bytes.Length
}

$target = Join-Path $root 'public\favicon.ico'
$stream = [System.IO.File]::Create($target)
$w = New-Object System.IO.BinaryWriter($stream)
$w.Write([UInt16]0); $w.Write([UInt16]1); $w.Write([UInt16]$icoFrames.Count)
foreach ($e in $entries) {
    $dim = $(if ($e[0] -ge 256) { 0 } else { $e[0] })
    $w.Write([Byte]$dim); $w.Write([Byte]$dim); $w.Write([Byte]0); $w.Write([Byte]0)
    $w.Write([UInt16]1); $w.Write([UInt16]32)
    $w.Write([UInt32]$e[1].Length); $w.Write([UInt32]$e[2])
}
foreach ($e in $entries) { $w.Write($e[1]) }
$w.Flush(); $w.Close(); $stream.Close()

foreach ($f in $icoFrames) { $f[2].Dispose() }
$original.Dispose()
Remove-Item $tempDir -Recurse -Force

Write-Output "favicon.ico  $((Get-Item $target).Length) bytes"