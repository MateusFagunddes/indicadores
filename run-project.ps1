$ErrorActionPreference = "Stop"

$projectDir = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $projectDir

function Invoke-Step {
    param(
        [string]$Name,
        [scriptblock]$Action
    )

    Write-Host ""
    Write-Host "==> $Name" -ForegroundColor Cyan

    try {
        & $Action

        if ($LASTEXITCODE -ne 0) {
            throw "Comando falhou com codigo de saida $LASTEXITCODE"
        }
    }
    catch {
        Write-Error "Falha em: $Name"
        throw
    }
}

function Test-Command {
    param([string]$Name)

    $command = Get-Command $Name -ErrorAction SilentlyContinue
    if (-not $command) {
        throw "Ferramenta '$Name' nao foi encontrada no PATH. Instale e tente novamente."
    }
}

try {
    foreach ($tool in @("php", "composer", "npm")) {
        Test-Command $tool
    }

    if (-not (Test-Path "composer.json")) {
        throw "Arquivo composer.json nao encontrado na pasta do projeto."
    }

    if (-not (Test-Path "package.json")) {
        throw "Arquivo package.json nao encontrado na pasta do projeto."
    }

    if (-not (Test-Path ".env")) {
        if (Test-Path ".env.example") {
            Copy-Item ".env.example" ".env"
            Write-Host ".env criado a partir do .env.example" -ForegroundColor Yellow
        }
        else {
            throw "Arquivo .env.example nao foi encontrado."
        }
    }

    Invoke-Step "Instalando dependencias do PHP..." {
        composer install --no-interaction --no-progress
    }

    Invoke-Step "Instalando dependencias do frontend..." {
        npm install
    }

    Invoke-Step "Gerando chave da aplicacao..." {
        php artisan key:generate --force
    }

    Invoke-Step "Executando migracoes do banco..." {
        php artisan migrate --force
    }

    Write-Host ""
    Write-Host "Abrindo servidor Laravel e Vite..." -ForegroundColor Green

    Start-Process powershell -ArgumentList "-NoExit", "-Command", "Set-Location '$projectDir'; php artisan serve --host=127.0.0.1 --port=8000"
    Start-Process powershell -ArgumentList "-NoExit", "-Command", "Set-Location '$projectDir'; npm run dev"

    Write-Host ""
    Write-Host "Projeto inciado com sucesso!" -ForegroundColor Green
    Write-Host "Acesse: http://127.0.0.1:8000" -ForegroundColor Green
}
catch {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Red
    Write-Host "Erro ao iniciar o projeto." -ForegroundColor Red
    Write-Host "Detalhes: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "========================================" -ForegroundColor Red
    exit 1
}
