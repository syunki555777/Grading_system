# 眠気評定システム
PHP環境を用いて、スマートフォンやPCのブラウザから簡単に眠気評定が行えるシステムです。

## なぜ作った？
眠気評定者への負担が大きかったため、通勤通学中でも評定を行えるように簡単なシステムで開発をしました。

## 何ができる？
Google のSheetAPIを用いてSpreadSheet上に評定を記録することができます。(所定のフォーマットに従ったファイルが必要です。)
負担を減らすためにキーボード操作等を可能にしています。

### ショートカット
1,2,3,4,5がそれぞれのボタンに対応しています。
Enter で値を送信できます。

## 注意事項
php側からGoogleへのアクセスが必要です。また、Googleアクセス用のjsonを別途生成する必要があります。
SheetAPIの利用制限があるので大規模な評定には向きません。

Google APIの権利はGoogle社に帰属します。

## English

# Drowsiness Rating System
This is a system that allows you to easily evaluate sleepiness from a browser on a smartphone or PC using a PHP environment.

## Why was it created?
The burden on the sleepiness evaluators was significant, so we developed a simple system that allows evaluations to be conducted even while commuting or attending school.

## What can it do?
Using Google's SheetAPI, you can record evaluations on a Spreadsheet. (A file following a predetermined format is required.) To reduce the burden, keyboard operations are also possible.
### Shortcuts
The numbers 1, 2, 3, 4, 5 correspond to each button. You can send the value using Enter.

## Attention
Access to Google from the php side is required. Also, you will need to generate a separate json for Google access. There are usage restrictions on the SheetAPI, so it is not suitable for large-scale evaluations.
The rights to the Google API belong to Google.
