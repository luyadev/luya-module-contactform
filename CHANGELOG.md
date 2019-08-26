# LUYA MODULE CONTACTFORM CHANGELOG

All notable changes to this project will be documented in this file. This project make usage of the [Yii Versioning Strategy](https://github.com/yiisoft/yii2/blob/master/docs/internals/versions.md).

## 1.0.12

+ [#24](https://github.com/luyadev/luya-module-contactform/issues/24) Fixed bug when using modelClass and sendTouserEmail is not defined.

## 1.0.11 (8. April 2019)

+ [#23](https://github.com/luyadev/luya-module-contactform/issues/23) Added new modelClass propertie to define a path to a given model instead of define the model on-th-fly.

## 1.0.10 (18. December 2018)

+ [#22](https://github.com/luyadev/luya-module-contactform/issues/22) Added option for recipient callback with model context.
+ Setup travis and code climate

## 1.0.9 (23. October 2018)

+ [#19](https://github.com/luyadev/luya-module-contactform/pull/19) Use renderAjax when calling via an ajax request.

## 1.0.8.1 (4. June 2018)

+ [#17](https://github.com/luyadev/luya-module-contactform/issues/17) Fixed bug with wrong declared new line chars.

## 1.0.8 (22. May 2018)

+ [#16](https://github.com/luyadev/luya-module-contactform/issues/16) Added option to configure footer message and mail template.

## 1.0.7 (24. January 2018)

+ [#15](https://github.com/luyadev/luya-module-contactform/issues/15) Fixed issue where rule options are not passed to rule generator.

## 1.0.6 (2. January 2018)

+ [#6](https://github.com/luyadev/luya-module-contactform/issues/6) Use robots behavior, use module translation system, use system mailer replyTo.
+ [#14](https://github.com/luyadev/luya-module-contactform/issues/14) Added default views for the frontend rendering.

## 1.0.5 (28. Nov 2017)

+ E-Mail altBody is now auto generated for not html compatible clients or libraries.

## 1.0.4 (1. Nov 2017)

- [#12](https://github.com/luyadev/luya-module-contactform/issues/12) Fixed issue where array input causes email generation error.

## 1.0.3 (25. May 2017)

+ [#5](https://github.com/luyadev/luya-module-contactform/issues/5) ReplyTo will auto detected.
+ Prepared Module for translations and added German language.

## 1.0.2 (4. May 2017)

+ [#4](https://github.com/luyadev/luya-module-contactform/issues/4) Used yii\widgets\DetailView in order to render E-Mails.
+ Added basic Unit-Tests.

## 1.0.1 (3. May 2017)

+ [#3](https://github.com/luyadev/luya-module-contactform/issues/3) Removed method `adresses()` due to RC3 upgrade conflict. 

## 1.0.0 (25. April 2017)

+ First stable release.
