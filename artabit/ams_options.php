<?php

/**
 * ©2014 Artabit
 * 
 * Permission is hereby granted to any person obtaining a copy of this software
 * and associated documentation for use and/or modification in association with
 * the artabit.com service.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 * AMS osCommerce payment plugin using the artabit.com service.
 * 
 */


global $amsOptions;

// Please look carefully through these options and adjust according to your installation.  
// Alternatively, most of these options can be dynamically set upon calling the functions in bp_lib.


// REQUIRED Api token you created at ams.artabit.com
// example: $amsOptions['apiToken'] = 'ded6b3b8990e46cd9d77be8a1b7b79b1';
$amsOptions['apiToken'] = '';

// REQUIRED Api secret you created at ams.artabit.com
// example: $amsOptions['apiSecret'] = 'UPcDrUr9BbkdRU/ljvhwZbuYPwa3IFkZijTn8ULL1c4=';
$amsOptions['apiSecret'] = '';

// url where the customer should be directed to after paying for the order
# example: $amsOptions['returnUrl'] = 'http://www.example.com/return.php';
$amsOptions['returnUrl'] = '';

// url where the customer should be directed to after cancelling for the order
# example: $amsOptions['cancelUrl'] = 'http://www.example.com/cancel.php';
$amsOptions['cancelUrl'] = '';

// url where ams server should send update callback.  See API doc for more details.
# example: $amsOptions['callbackUrl'] = 'http://www.example.com/ams_callback.php';
$amsOptions['callbackUrl'] = '';

// public key of ams server used to verify incoming message / callback
$amsOptions['amsPublicKey'] = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuIQsJFLz8uMp5TB/ckzG
qtPf+3xVAO/9mfulx9jIOQMjyKpMquIgIfrCAbIWJQfV6TbOMVni5+T35e/dK94V
ip13NfFmakuKH6LS3OKj1/EDonF6EyMhABZJFK933bkS/+MXOJY8r2iVO1xbzHba
oMKaZkTWE1Gov0fW+ni+M45BUbPdwN7eRSYWMzKDVCgiRQA6mMQJRJ2S4F0bq6UN
9sDRRgffdUjQ0Tb8Nl64skTOjCo7x4isC6YR6GmIaWcQwquD7HGSP7UnnVw3U8ry
mP+kepZ5jGJH2xEXPjau0ipfNsLgXcP8yDjMnVnNgHkHzYSzdoB/NOj0Gnymdp6+
DQIDAQAB
-----END PUBLIC KEY-----
';

?>
