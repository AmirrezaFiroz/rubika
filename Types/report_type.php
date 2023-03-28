<?php

namespace Rubika\Types;

/**
 * report type value
 * other : سایر
 * violence : خشونت آمیز
 * spam : هرزنامه
 * pornography : محتوای مستهجن
 * child_abuse : کودک آزاری
 * copyright : نقض قوانین کپی رایت
 * fishing : کلاهبرداری
 */
enum report_type: int
{
    case Other = 100;
    case Violence = 101;
    case Spam = 102;
    case Pornography = 103;
    case Child_abuse = 104;
    case Copyright = 105;
    case Fishing = 106;
}
