<?php

# Copyright (c)  2020 - <mlunzena@uos.de>
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.

class StatsdMetricsSettings
{
    const CONFIG_KEY = 'STATSD_SETTINGS';

    public static function get()
    {
        $cfg = Config::get();
        if (!isset($cfg[self::CONFIG_KEY]) || '' === $cfg[self::CONFIG_KEY]) {
            return array();
        }

        $decoded = array();
        foreach (json_decode($cfg[self::CONFIG_KEY]) as $k => $v) {
            $decoded[$k] = $v;
        }

        return $decoded;
    }


    public static function set($settings)
    {
        self::ensure();

        $encoded = array();
        foreach ($settings as $k => $v) {
            $encoded[$k] = $v;
        }

        return Config::get()->store(self::CONFIG_KEY, json_encode($encoded));
    }


    public static function ensure()
    {
        $exists = ConfigEntry::findByField(self::CONFIG_KEY);
        if ($exists) {
            return;
        }

        Config::get()->create(self::CONFIG_KEY);
    }
}
