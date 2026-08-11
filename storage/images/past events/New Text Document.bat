@echo off
for /d %%D in (*) do (
    mkdir "%%D\thumb"
)
echo Done!
pause