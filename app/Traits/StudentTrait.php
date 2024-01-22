<?php

namespace App\Traits;

trait StudentTrait{
    public function isValidEnrollment($enrollment)
    {
        if (!preg_match('/^(?:[0-9]{2}[abAB][0-9]{7,8}|[sS][aA][0-9]{7})$/', $enrollment)) {
            return false;
        }
        return true;
    }
    
    public function isValidCurp($curp){
        if(!preg_match('/^([A-Z][AEIOUX][A-Z]{2}\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])[HM](?:AS|B[CS]|C[CLMSH]|D[FG]|G[TR]|HG|JC|M[CNS]|N[ETL]|OC|PL|Q[TR]|S[PLR]|T[CSL]|VZ|YN|ZS)[B-DF-HJ-NP-TV-Z]{3}[A-Z\d])(\d)$/', $curp)){
            return false;
        }
        return true;
    }
}