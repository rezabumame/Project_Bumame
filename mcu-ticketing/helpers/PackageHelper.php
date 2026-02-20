<?php
class PackageHelper {
    private static function parse($raw) {
        $raw = str_replace(["\r\n", "\r"], "\n", $raw);
        $lines = explode("\n", $raw);
        $packages = [];
        $current_package = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Check for package header (starts with bullet or contains 'paket')
            $is_header = (strpos($line, '•') === 0) || (stripos($line, 'paket') === 0);
            
            if ($is_header) {
                if ($current_package) {
                    $packages[] = $current_package;
                }
                $pkg_name = preg_replace('/^•\s*/', '', $line);
                $current_package = [
                    'name' => $pkg_name,
                    'items' => []
                ];
            } elseif (strpos($line, '-') === 0) {
                if ($current_package) {
                    $item_text = preg_replace('/^-\s*/', '', $line);
                    $current_package['items'][] = $item_text;
                }
            } else {
                if ($current_package) {
                    $current_package['items'][] = $line;
                }
            }
        }
        if ($current_package) {
            $packages[] = $current_package;
        }
        return $packages;
    }

    public static function renderMatrix($exam_types_raw, $company_name = '') {
        // Check structure first
        $has_structure = (strpos($exam_types_raw, '•') !== false) || (stripos($exam_types_raw, 'paket') !== false);
        if (!$has_structure) {
            return self::render($exam_types_raw);
        }

        $packages = self::parse($exam_types_raw);
        if (empty($packages)) {
            return self::render($exam_types_raw);
        }

        // Collect all unique items
        $allItems = [];
        foreach ($packages as $pkg) {
            foreach ($pkg['items'] as $item) {
                // Clean item text (remove B2B pax info if present)
                $cleanItem = preg_replace('/\s*\((B2B|b2b).*?\)/', '', $item);
                // Remove numeric code prefix (e.g. "910016 - ")
                $cleanItem = preg_replace('/^\d+\s*-\s*/', '', $cleanItem);
                
                // Special rename for Konsultasi Dokter Umum
                if (stripos($cleanItem, 'Konsultasi Dokter Umum') !== false) {
                    $cleanItem = 'Pemeriksaan Fisik';
                }
                
                $key = strtolower(trim($cleanItem));
                if (!isset($allItems[$key])) {
                    $allItems[$key] = trim($cleanItem); // Store clean display name
                }
            }
        }
        
        // Start HTML
        $html = '<div class="table-responsive rounded shadow-sm border" style="max-width: 100%; overflow-x: auto;">';
        $html .= '<table class="table table-bordered table-hover align-middle mb-0" style="min-width: 800px; border-collapse: separate; border-spacing: 0; font-size: 1.05rem;">';
        
        // Header
        $html .= '<thead class="bg-light">';
        $html .= '<tr>';
        // Sticky first column header - Added align-middle text-center
        $html .= '<th class="text-center align-middle py-3 ps-4 bg-white text-dark border-end shadow-sm" style="position: sticky; left: 0; z-index: 20; min-width: 350px; width: 35%;">Jenis Pemeriksaan</th>';
        
        foreach ($packages as $pkg) {
            $pkgName = $pkg['name'];
            
            // Auto-delete company name
            if (!empty($company_name)) {
                $pkgName = str_ireplace($company_name, '', $pkgName);
            }
            // Remove "Paket" word if it exists at start
            $pkgName = preg_replace('/^Paket\s+/i', '', $pkgName);
            // Clean up double spaces or weird punctuation
            $pkgName = trim($pkgName);
            
            // Extract Pax count to force new line
            $paxPart = '';
            // Look for pattern like (110 pax) at the end
            if (preg_match('/(\(\d+\s*pax\))$/i', $pkgName, $matches)) {
                $paxPart = $matches[1];
                $pkgName = str_replace($paxPart, '', $pkgName);
                $pkgName = trim($pkgName);
            }
            
            // Added border-start for vertical line separator
            $html .= '<th class="py-3 text-center bg-light text-dark fw-bold border-start" style="min-width: 150px;">';
            $html .= htmlspecialchars($pkgName);
            if (!empty($paxPart)) {
                $html .= '<br><span class="text-nowrap">' . htmlspecialchars($paxPart) . '</span>';
            }
            $html .= '</th>';
        }
        $html .= '</tr>';
        $html .= '</thead>';
        
        // Body
        $html .= '<tbody>';
        $rowIndex = 0;
        foreach ($allItems as $key => $displayName) {
            $rowBg = ($rowIndex % 2 === 0) ? 'bg-white' : 'bg-light';
            $html .= '<tr class="' . $rowBg . '">';
            // Sticky first column cell
            $html .= '<td class="fw-medium text-secondary ps-4 ' . $rowBg . ' border-end shadow-sm" style="position: sticky; left: 0; z-index: 10;">';
            $html .= '<div style="line-height: 1.5;">';
            $html .= htmlspecialchars($displayName);
            $html .= '</div>';
            $html .= '</td>';
            
            foreach ($packages as $pkg) {
                $hasItem = false;
                foreach ($pkg['items'] as $pkgItem) {
                    // Apply same cleaning logic to match key
                    $cleanPkgItem = preg_replace('/\s*\((B2B|b2b).*?\)/', '', $pkgItem);
                    $cleanPkgItem = preg_replace('/^\d+\s*-\s*/', '', $cleanPkgItem);
                    
                    if (stripos($cleanPkgItem, 'Konsultasi Dokter Umum') !== false) {
                        $cleanPkgItem = 'Pemeriksaan Fisik';
                    }

                    if (strtolower(trim($cleanPkgItem)) === $key) {
                        $hasItem = true;
                        break;
                    }
                }
                
                // Added border-start for vertical line separator with row background
                $html .= '<td class="text-center border-start ' . $rowBg . '">';
                if ($hasItem) {
                    $html .= '<i class="fas fa-check-circle fs-5" style="color: #204EAB;"></i>';
                } else {
                    $html .= '<span class="text-secondary opacity-25 rounded-circle d-inline-block" style="width: 8px; height: 8px; background-color: currentColor;"></span>';
                }
                $html .= '</td>';
            }
            $html .= '</tr>';
            $rowIndex++;
        }
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        
        return $html;
    }

    public static function render($exam_types_raw) {
        // Normalize newlines
        $exam_types_raw = str_replace(["\r\n", "\r"], "\n", $exam_types_raw);
        
        // Check if it looks like a list or package structure
        $has_structure = (strpos($exam_types_raw, '•') !== false) || (stripos($exam_types_raw, 'paket') !== false);
        
        if (!$has_structure && !empty($exam_types_raw)) {
            // No structure, just show text
            return '<div class="p-3 bg-light border rounded text-secondary">' . nl2br(htmlspecialchars($exam_types_raw)) . '</div>';
        }

        $packages = self::parse($exam_types_raw);
        
        if (empty($packages)) {
             return '<div class="text-muted small fst-italic">No exam type data available</div>';
        }

        // Simple list view (Removed accordion/hide logic)
        $html = '<div class="d-flex flex-column gap-3">';
        foreach ($packages as $pkg) {
            $pkgName = htmlspecialchars($pkg['name']);
            
            $html .= '<div class="border rounded shadow-sm overflow-hidden">';
            $html .= '<div class="bg-light px-3 py-2 fw-bold text-dark border-bottom">';
            $html .= $pkgName;
            $html .= '</div>';
            
            if (!empty($pkg['items'])) {
                $html .= '<div class="bg-white">';
                foreach($pkg['items'] as $item) {
                    $itemText = htmlspecialchars($item);
                    $html .= '<div class="px-3 py-2 border-bottom small text-secondary d-flex align-items-start">';
                    $html .= '<i class="fas fa-check text-primary mt-1 me-2" style="font-size: 0.8em;"></i>';
                    $html .= '<span>' . $itemText . '</span>';
                    $html .= '</div>';
                }
                $html .= '</div>';
            } else {
                $html .= '<div class="p-3 text-muted small fst-italic">No items listed</div>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        
        return $html;
    }
}
