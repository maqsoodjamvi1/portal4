@echo off
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0upload-changes.ps1" %*
