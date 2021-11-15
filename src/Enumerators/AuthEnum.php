<?php

namespace WebDEV\Meli\Enumerators;

class AuthEnum {
    const MLA = 'https://auth.mercadolibre.com.ar'; // Argentina
    const MLB = 'https://auth.mercadolivre.com.br'; // Brazil
    const MCO = 'https://auth.mercadolibre.com.co'; // Colombia
    const MCR = 'https://auth.mercadolibre.co.cr';  // Costa Rica
    const MEC = 'https://auth.mercadolibre.com.ec'; // Ecuador
    const MLC = 'https://auth.mercadolibre.cl';     // Chile
    const MLM = 'https://auth.mercadolibre.com.mx'; // Mexico
    const MLU = 'https://auth.mercadolibre.com.uy'; // Uruguay
    const MLV = 'https://auth.mercadolibre.com.ve'; // Venezuela
    const MPA = 'https://auth.mercadolibre.com.pa'; // Panama
    const MPE = 'https://auth.mercadolibre.com.pe'; // Peru
    const MPT = 'https://auth.mercadolibre.com.pt'; // Portugal
    const MRD = 'https://auth.mercadolibre.com.do'; // Dominican Republic
    const CBT = 'https://global-selling.mercadolibre.com'; // Global Selling

    public static function toArray(): array {
        return [
            self::MLA => 'Argentina',
            self::MLB => 'Brazil',
            self::MCO => 'Colombia',
            self::MCR => 'Costa Rica',
            self::MEC => 'Ecuador',
            self::MLC => 'Chile',
            self::MLM => 'Mexico',
            self::MLU => 'Uruguay',
            self::MLV => 'Venezuela',
            self::MPA => 'Panama',
            self::MPE => 'Peru',
            self::MPT => 'Portugal',
            self::MRD => 'Dominican Republic',
            self::CBT => 'Global Selling',
        ];
    }
}
