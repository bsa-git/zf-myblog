<?php

/**
 * Default_Plugin_SimpleDate
 *
 * Класс для работы с датами
 *
 *
 * @uses
 * @package    Module-Default
 * @subpackage Plugins
 */
class Default_Plugin_SimpleDate {

    //
    // Private / protected
    //
    private $date = FALSE;
    private $mdays = array(0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    private $daynum_abbrev = array("Sun" => 0, "Mon" => 1, "Tue" => 2, "Wed" => 3, "Thu" => 4, "Fri" => 5, "Sat" => 6);
    private $daynum_full = array("Sunday" => 0, "Monday" => 1, "Tuesday" => 2, "Wednesday" => 3, "Thurday" => 4, "Friday" => 5, "Saturday" => 6);

    //-------------------------------------------------
    
    //
    // Public
    // 
    // Можно задавать текущую дату если нет параметров
    // если парамет целое число типа timestamp
    // или параметр строка в виде YYYY-MM-DD
    //
    public function __construct($todate=FALSE) {
        $this->set($todate);
    }

    // get / set
    public function set($todate=FALSE) {
        $this->date = $this->default_value($todate, date('Y-m-d'));
        $this->make_valid();
    }

    public function get($todate=FALSE) {
        return $this->date;
    }
    
    // namespace UniXolutions\date
    // simple, helper funcs
    private function default_value($val='', $def_val='') {
        if(strlen($val) < 1){
            $val = $def_val;
        }  else {
            if(is_integer($val)){
               $val = date('Y-m-d', $val);
            }
        }
        //return ((strlen($val) < 1) ? $def_val : $val);
        return $val;
    }

    private function bool_value($val='') {
        $v = strtoupper($this->default_value($val, 'off'));
        if ($v == 'OFF' || $v == 'NO')
            return 0;
        else if ($v == 'Y' || $v == 'YES')
            return 1;
        else if ($v == 'ON' || $v == 'YES')
            return 1;
        else if ($v + 0 > 0 || $v + 0 < 0)
            return 1;
        return 0;
    }

    protected function make_valid() {
        list($yr, $mt, $dt) = explode("-", $this->date);
        $yr += 0;
        $mt += 0;
        $dt += 0;
        if ($dt < 1)
            $dt = 1;
        if ($mt < 1)
            $mt = 1; if ($mt > 12)
            $mt = 12;
        if ($yr < 1)
            $yr = 1; if ($yr > 9999)
            $yr = 9999;
        if ($mt == 2) {
            if ($this->isleap()) {
                if ($dt > 29)
                    $dt = 29;
            } else {
                if ($dt > 28)
                    $dt = 28;
            }
        } else {
            if ($dt > $this->mdays[$mt])
                $dt = $this->mdays[$mt];
        }
        $this->date = sprintf("%04d-%02d-%02d", $yr, $mt, $dt);
    }

    // bool
    public function isleap() {
        $y = $this->year_number();
        return $this->bool_value((($y % 400 == 0 || $y % 4 == 0 && $y % 100 != 0) ? TRUE : FALSE));
    }

    // names
    public function day_name_abbrev() {
        return date("D", $this->to_unix());
    }

    public function day_name_full() {
        return date("l", $this->to_unix());
    }

    public function month_name_abbrev() {
        return date("M", $this->to_unix());
    }

    public function month_name_full() {
        return date("F", $this->to_unix());
    }

    // numbers
    public function to_unix() {
        return mktime(0, 0, 0, substr($this->date, 5, 2), substr($this->date, 8, 2), substr($this->date, 0, 4));
    }

    /**
     * День недели
     * @return int
     */
    public function day_number() {
        return date("w", $this->to_unix());
    }

    /**
     * День месяца
     * @return int
     */
    public function day_month() {
        list($yr, $mt, $dt) = explode("-", $this->date);
        return $dt + 0;
    }

    public function week_number() {
        return date("W", $this->add_days(-1 * $this->day_number())->to_unix());
    }

    public function month_number() {
        list($yr, $mt, $dt) = explode("-", $this->date);
        return $mt + 0;
    }

    public function year_number() {
        list($yr, $mt, $dt) = explode("-", $this->date);
        return $yr + 0;
    }

    // arithmetic
    public function add_days($days) {
        return new SimpleDate(date("Y-m-d", $this->to_unix() + $days * (86400)));
    }

    public function add_weeks($weeks) {
        return $this->add_days(7 * ($weeks));
    }

    public function add_biweeks($biweeks) {
        return $this->add_days(7 * ($biweeks) * 2);
    }

    public function add_months($months) {
        list($yr, $mt, $dt) = explode("-", $this->date);
        $_t = mktime(0, 0, 0, (date($months) + $mt), $dt, $yr);
        return new SimpleDate(date('Y-m-d', $_t));
    }

    public function add_years($years) {
        list($yr, $mt, $dt) = explode("-", $this->date);
        return new SimpleDate(sprintf("%04d-%02d-%02d", $yr + $years, $mt, $dt));
    }

    public function diff_days($todate) {
        $dt = new SimpleDate($todate);
        return (int) (($dt->to_unix() - $this->to_unix()) / (86400));
    }

    public function diff_weeks($todate) {
        $dt = new SimpleDate($todate);
        return (int) (($dt->to_unix() - $this->to_unix()) / (86400 * 7));
    }

    public function diff_month($todate) {
        $to = new SimpleDate($todate);
        $m1 = $this->month_number();
        $y1 = $this->year_number();
        $m2 = $to->month_number();
        $y2 = $to->year_number();
        return (($m2 < $m1) ? ($y2 - $y1 - 1) : ($y2 - $y1)) * 12 + (12 + ($m2 - $m1)) % 12;
    }

    // offsets
    public function date_begin_of_month() {
        list($y, $m, $d) = explode("-", $this->date);
        return new SimpleDate(sprintf("%04d-%02d-01", $y, $m));
    }

    public function date_15_of_month() {
        list($y, $m, $d) = explode("-", $this->date);
        return new SimpleDate(sprintf("%04d-%02d-15", $y, $m));
    }

    public function date_end_of_month() {
        list($y, $m, $d) = explode("-", $this->date);
        if ($m == 2 && $this->isleap())
            $d = 29; else
            $d=$this->mdays[(int) $m]; return new SimpleDate(sprintf("%04d-%02d-%02d", $y, $m, $d));
    }

    public function date_last_dayname_of_month($dayname) {
        return $this->date_last_daynum_of_month($this->daynum_abbrev[$dayname]);
    }

    public function date_last_daynum_of_month($daynum) {
        // get last e.g. Thu (4) of month, Sun-0,Mon-1,Tue-2,Wed-3,Thu-4,Fri-5,Sat-6
        $dt = $this->date_end_of_month();
        $num = $dt->day_number();
        $days = (($num < $daynum) ? -1 * (7 + $num - $daynum) % 7 : $daynum - $num);
        return $dt->add_days($days);
    }

    public function date_num_dayname_of_month($number, $dayname) {
        return $this->date_num_daynum_of_month($number, $this->daynum_abbrev[$dayname]);
    }

    public function date_num_daynum_of_month($number, $daynum) {
        // get the e.g. third Tuesday (2) of a month
        $dt = $this->date_begin_of_month();
        $num = $dt->day_number();
        $days = (($num < $daynum) ? $daynum - $num : (7 - $num + $daynum) % 7) + ($number - 1) * 7;
        return $dt->add_days($days);
    }

    // display
    public function to_display_date() {
        return date('d.m.Y', $this->to_unix());
    }

    /**
     * Получить диапазон дат для
     * определенного диапазона месяцев
     * с учетом максимальных дат каждого месяца
     *
     * @param int $aCountMonth
     * @return array
     */
    public function getDateRange($aCountMonth) {
        $arrDates = array();
        //-------------------
        $arrDates[] = $this->get();
        //Получим день месяца
        $day = $this->day_month();

        //Получим дату с 1 днем
        list($y, $m, $d) = explode("-", $this->date);
        $strDate = $y . '-' . $m . '-' . '01';
        $dt = new SimpleDate($strDate);
        //Получим диапазон дат для последующих месяцев
        for ($index = 1; $index <= $aCountMonth; $index++) {
            list($y, $m, $d) = explode("-", $dt->date);
            $strDate = $y . '-' . $m . '-' . '01';
            $dt = new SimpleDate($strDate);
            //Получим новую дату для следующего месяца
            $newDate = $dt->add_months(1);
            $dt = $newDate->date_end_of_month();
            $maxDay = $dt->day_month();
            if ($day > $maxDay) {
                $arrDates[] = $dt->get();
            } else {
                list($y, $m, $d) = explode("-", $dt->get());
                $strDate = $y . '-' . $m . '-' . $day;
                $dt = new SimpleDate($strDate);
                $arrDates[] = $dt->get();
            }
        }
        return $arrDates;
    }

}
?>