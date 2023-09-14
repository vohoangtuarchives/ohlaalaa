# Source Web Ohlaalaa
```
https://github.com/vohoangtuarchives/ohlaalaa
```
# Source convert shoping point

```
https://github.com/vohoangtuarchives/ohlaalaa_transform_points
```
```
* * * * * php /main_web/artisan schedule:run >> /dev/null 2>&1
30 4 * * * curl  /other_source/admin/convertshoppingpoint &> /dev/null
```

Hiện việc convert shopping point đang ở một source khác, cùng sử dụng chung 1 database

