@echo off
cd ../
cd uploads
echo Y | del -f *.txt
echo Y | del  -f *.doc
echo Y | del -f *.xls
echo Y | del  -f *.pdf
echo Y | del  -f *.xml
echo Y | del  -f *.csv
echo 清除上传文件完毕
pause
exit
