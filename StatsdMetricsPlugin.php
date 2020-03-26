<?php

// Copyright (c)  2020 - <mlunzena@uos.de>
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.

class StatsdMetricsPlugin extends StudipPlugin implements MetricsPlugin
{
    public static function onEnable($pluginId)
    {
        RolePersistence::assignPluginRoles($pluginId, array(7));
    }

    public static function onDisable($pluginId)
    {
        PluginManager::getInstance()->unregisterPlugin($pluginId);
    }

    // ***** METRICSPLUGIN METHODS *****

    public function count($stat, $value, $sampleRate = null)
    {
        self::send($stat, intval($value) . '|c', $sampleRate);
    }

    public function timing($stat, $time, $sampleRate = null)
    {
        self::send($stat, intval($time) . '|ms', $sampleRate);
    }

    public function gauge($stat, $value, $sampleRate = null)
    {
        self::send($stat, intval($value) . '|g', $sampleRate);
    }

    // ***** PRIVATE STUFF *****

    // send the metrics over UDP
    private static function send($stat, $data, $sampleRate) {

        require_once 'StatsdMetricsSettings.php';

        if ($sampleRate < 1) {
            $data .= '|@' . $sampleRate;
        }

        $config = StatsdMetricsSettings::get();

        try {
            if (!$file = @fsockopen('udp://' . $config["ip"], $config["port"])) {
                return;
            }
            fwrite($file, $config["prefix"] . ".$stat:$data");
            fclose($file);
        } catch (Exception $exception) {
        }
    }
}
