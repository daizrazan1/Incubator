<?php
require_once 'db_config.php';

function checkCompatibility($buildParts) {
    $compatible = true;
    $issues = [];
    $partCompatibility = [];
    
    $cpu = null;
    $motherboard = null;
    $psu = null;
    $ram = null;
    $caseData = null;
    
    foreach ($buildParts as $part) {
        $partCompatibility[$part['part_id']] = ['compatible' => true, 'issues' => []];
        
        switch ($part['category']) {
            case 'CPU':
                $cpu = $part;
                break;
            case 'Motherboard':
                $motherboard = $part;
                break;
            case 'PSU':
                $psu = $part;
                break;
            case 'RAM':
                $ram = $part;
                break;
            case 'Case':
                $caseData = $part;
                break;
        }
    }
    
    if ($cpu && $motherboard) {
        if ($cpu['socket'] && $motherboard['socket']) {
            if ($cpu['socket'] !== $motherboard['socket']) {
                $compatible = false;
                $issue = "CPU socket ({$cpu['socket']}) doesn't match motherboard socket ({$motherboard['socket']})";
                $issues[] = $issue;
                $partCompatibility[$cpu['part_id']]['compatible'] = false;
                $partCompatibility[$cpu['part_id']]['issues'][] = $issue;
                $partCompatibility[$motherboard['part_id']]['compatible'] = false;
                $partCompatibility[$motherboard['part_id']]['issues'][] = $issue;
            }
        }
    }
    
    if ($ram && $motherboard) {
        $ramDDR = null;
        $mbDDR = null;
        
        if (preg_match('/DDR(\d+)/i', $ram['form_factor'] . ' ' . $ram['specs'] . ' ' . $ram['part_name'], $matches)) {
            $ramDDR = 'DDR' . $matches[1];
        }
        
        if (preg_match('/DDR(\d+)/i', $motherboard['form_factor'] . ' ' . $motherboard['specs'] . ' ' . $motherboard['part_name'], $matches)) {
            $mbDDR = 'DDR' . $matches[1];
        }
        
        if ($ramDDR && $mbDDR && $ramDDR !== $mbDDR) {
            $compatible = false;
            $issue = "RAM type ({$ramDDR}) is not compatible with motherboard ({$mbDDR})";
            $issues[] = $issue;
            $partCompatibility[$ram['part_id']]['compatible'] = false;
            $partCompatibility[$ram['part_id']]['issues'][] = $issue;
            $partCompatibility[$motherboard['part_id']]['compatible'] = false;
            $partCompatibility[$motherboard['part_id']]['issues'][] = $issue;
        }
    }
    
    if ($psu) {
        $totalWattage = 0;
        foreach ($buildParts as $part) {
            $partWattage = 0;
            
            if ($part['category'] === 'PSU') {
                continue;
            }
            
            if ($part['tdp']) {
                $partWattage = $part['tdp'];
            } elseif ($part['wattage']) {
                $partWattage = $part['wattage'];
            } else {
                if ($part['category'] === 'GPU' || $part['category'] === 'CPU') {
                    if (preg_match('/(\d+)\s*W/i', $part['specs'] ?? '', $matches)) {
                        $partWattage = intval($matches[1]);
                    }
                }
            }
            
            $totalWattage += $partWattage;
        }
        
        if ($psu['wattage'] && $totalWattage > 0) {
            $recommendedWattage = ceil($totalWattage * 1.2);
            
            if ($psu['wattage'] < $totalWattage) {
                $compatible = false;
                $issue = "PSU wattage ({$psu['wattage']}W) is insufficient for total system power ({$totalWattage}W)";
                $issues[] = $issue;
                $partCompatibility[$psu['part_id']]['compatible'] = false;
                $partCompatibility[$psu['part_id']]['issues'][] = $issue;
            } elseif ($psu['wattage'] < $recommendedWattage) {
                $warning = "PSU wattage ({$psu['wattage']}W) is below recommended ({$recommendedWattage}W with 20% headroom)";
                $issues[] = $warning;
            }
        }
    }
    
    if ($motherboard && $caseData) {
        if ($motherboard['form_factor'] && $caseData['form_factor']) {
            $mbFormFactor = strtoupper(trim($motherboard['form_factor']));
            $caseFFRaw = strtoupper($caseData['form_factor']);
            
            $formFactorPatterns = [
                'E-ATX' => '/\b(E[\s\-_]?ATX|EATX|EXTENDED[\s\-_]?ATX)\b/i',
                'MICRO-ATX' => '/\b(MICRO[\s\-_]?ATX|M[\s\-_]?ATX|MATX|uATX)\b/i',
                'MINI-ITX' => '/\b(MINI[\s\-_]?ITX|M[\s\-_]?ITX)\b/i',
                'ITX' => '/\b(?<!MINI[\s\-_])(?<!M[\s\-_])(ITX)\b/i',
                'ATX' => '/\b(?<!E[\s\-_])(?<!MICRO[\s\-_])(?<!M[\s\-_])(?<!MINI[\s\-_])(ATX)\b/i'
            ];
            
            $caseSupportedSizes = [];
            foreach ($formFactorPatterns as $standard => $pattern) {
                if (preg_match($pattern, $caseFFRaw)) {
                    if ($standard === 'ITX' && !in_array('MINI-ITX', $caseSupportedSizes)) {
                        $caseSupportedSizes[] = 'MINI-ITX';
                    } else {
                        $caseSupportedSizes[] = $standard;
                    }
                }
            }
            
            $mbStandard = null;
            foreach ($formFactorPatterns as $standard => $pattern) {
                if (preg_match($pattern, $mbFormFactor)) {
                    if ($standard === 'ITX') {
                        $mbStandard = 'MINI-ITX';
                    } else {
                        $mbStandard = $standard;
                    }
                    break;
                }
            }
            
            $formFactorHierarchy = [
                'E-ATX' => ['E-ATX', 'ATX', 'MICRO-ATX', 'MINI-ITX'],
                'ATX' => ['ATX', 'MICRO-ATX', 'MINI-ITX'],
                'MICRO-ATX' => ['MICRO-ATX', 'MINI-ITX'],
                'MINI-ITX' => ['MINI-ITX']
            ];
            
            $mbMatches = false;
            if ($mbStandard && !empty($caseSupportedSizes)) {
                foreach ($caseSupportedSizes as $caseSize) {
                    if (isset($formFactorHierarchy[$caseSize]) && in_array($mbStandard, $formFactorHierarchy[$caseSize])) {
                        $mbMatches = true;
                        break;
                    }
                }
            }
            
            if (!$mbMatches && !empty($caseSupportedSizes)) {
                $compatible = false;
                $issue = "Motherboard form factor ({$mbFormFactor}) may not fit in case (supports: " . implode(', ', $caseSupportedSizes) . ")";
                $issues[] = $issue;
                $partCompatibility[$motherboard['part_id']]['compatible'] = false;
                $partCompatibility[$motherboard['part_id']]['issues'][] = $issue;
                $partCompatibility[$caseData['part_id']]['compatible'] = false;
                $partCompatibility[$caseData['part_id']]['issues'][] = $issue;
            }
        }
    }
    
    return [
        'overall_compatible' => $compatible,
        'issues' => $issues,
        'part_compatibility' => $partCompatibility
    ];
}
?>
