# phpbb-ext-geomoderate

[![Build Status](https://travis-ci.org/gothick/phpbb-ext-geomoderate.svg?branch=master)](https://travis-ci.org/gothick/phpbb-ext-geomoderate)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gothick/phpbb-ext-geomoderate/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/gothick/phpbb-ext-geomoderate/?branch=master)

**Note**: This is beta software. Don't install it on a production board. Currently it's compatible with phpBB 3.1.x ("Ascraeus"), and it seems to work for me, but your mileage very definitely may vary.

A phpBB Extension that moderates all posts from certain countries, based on the Maxmind GeoLite2 Country database.

* Install in the normal way.
* Configure under Extensions->GeoModerate Settings. Tick the box for any country of origin whose posts you'd like to go to the moderation queue.
* Profit.

Admins and moderators will bypass the check automatically. Any moderation action taken by the Extension will appear in the Moderation log.

This product includes GeoLite2 data created by MaxMind, available from [http://www.maxmind.com](http://www.maxmind.com).
