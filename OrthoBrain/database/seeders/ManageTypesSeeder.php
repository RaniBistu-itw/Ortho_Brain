<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductSubcategory;
use App\Models\Scanner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ManageTypesSeeder extends Seeder
{
    private const STORAGE_DISK = 'public';
    private const IMAGE_FOLDER = 'products';
    private const IMG_W = 400;
    private const IMG_H = 300;

    public function run(): void
    {
        $this->seedCategories();
        $this->seedScanners();
    }

    // -------------------------------------------------------------------------
    // Product categories  →  subcategories  →  products
    // -------------------------------------------------------------------------

    private function seedCategories(): void
    {
        foreach ($this->categoryData() as $catName => $catData) {
            $category = ProductCategory::firstOrCreate(
                ['name' => $catName],
                ['status' => 'ACTIVE']
            );

            foreach ($catData['subcategories'] as $subName => $products) {
                $subcategory = ProductSubcategory::firstOrCreate(
                    ['category_id' => $category->id, 'name' => $subName],
                    [
                        'description' => $catData['description'] ?? null,
                        'status'      => true,
                    ]
                );

                foreach ($products as $productData) {
                    $product = Product::firstOrCreate(
                        [
                            'name'        => $productData['name'],
                            'category_id' => $category->id,
                        ],
                        [
                            'subcategory_id'      => $subcategory->id,
                            'base_price'          => $productData['price'],
                            'number_of_revisions' => $productData['revisions'] ?? null,
                            'product_term_months' => $productData['term_months'] ?? null,
                            'from_step'           => $productData['from_step'] ?? null,
                            'to_step'             => $productData['to_step'] ?? null,
                            'description'         => $productData['description'] ?? null,
                            'status'              => 'ACTIVE',
                        ]
                    );

                    // Only add a placeholder image when the product has none yet.
                    if ($product->images()->doesntExist()) {
                        $key = $this->makePlaceholderImage(
                            $productData['name'],
                            $productData['color'] ?? '#4A90D9'
                        );

                        if ($key) {
                            ProductImage::create([
                                'product_id' => $product->id,
                                's3_key'     => $key,
                                'sort_order' => 0,
                            ]);
                        }
                    }
                }
            }
        }
    }

    // -------------------------------------------------------------------------
    // Scanners
    // -------------------------------------------------------------------------

    private function seedScanners(): void
    {
        foreach ($this->scannerData() as $scanner) {
            Scanner::firstOrCreate(
                ['name' => $scanner['name']],
                [
                    'description'     => $scanner['description'],
                    'portal_link'     => $scanner['portal_link'] ?? null,
                    'portal_password' => null,
                    'status'          => 'ACTIVE',
                ]
            );
        }
    }

    // -------------------------------------------------------------------------
    // Placeholder image generator  (GD — available in all standard PHP builds)
    // -------------------------------------------------------------------------

    private function makePlaceholderImage(string $label, string $hexColor): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $disk = \Illuminate\Support\Facades\Storage::disk(self::STORAGE_DISK);
        $key  = self::IMAGE_FOLDER . '/' . Str::uuid() . '.png';

        [$r, $g, $b] = $this->hexToRgb($hexColor);

        $img = imagecreatetruecolor(self::IMG_W, self::IMG_H);

        $bg      = imagecolorallocate($img, $r, $g, $b);
        $overlay = imagecolorallocatealpha($img, 0, 0, 0, 40);
        $white   = imagecolorallocate($img, 255, 255, 255);
        $light   = imagecolorallocate($img, 220, 220, 220);

        // Background
        imagefill($img, 0, 0, $bg);

        // Subtle bottom gradient strip
        imagefilledrectangle($img, 0, self::IMG_H - 60, self::IMG_W, self::IMG_H, $overlay);

        // Centered label (wrap at ~32 chars per line)
        $lines    = $this->wrapText($label, 32);
        $lineH    = 18;
        $totalH   = count($lines) * $lineH;
        $startY   = (int) ((self::IMG_H - $totalH) / 2);

        foreach ($lines as $i => $line) {
            $textW = imagefontwidth(4) * strlen($line);
            $x     = (int) ((self::IMG_W - $textW) / 2);
            imagestring($img, 4, $x, $startY + $i * $lineH, $line, $white);
        }

        // "PLACEHOLDER" watermark at the bottom
        $wLabel = 'PLACEHOLDER';
        $wW     = imagefontwidth(2) * strlen($wLabel);
        imagestring($img, 2, (int) ((self::IMG_W - $wW) / 2), self::IMG_H - 18, $wLabel, $light);

        // Capture PNG into a buffer then store via Storage facade (works for both
        // local "public" disk and S3 without any path manipulation).
        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        $disk->put($key, $png);

        return $key;
    }

    /** @return int[] */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    /** @return string[] */
    private function wrapText(string $text, int $maxChars): array
    {
        $words = explode(' ', $text);
        $lines = [];
        $line  = '';

        foreach ($words as $word) {
            if (strlen($line . ' ' . $word) > $maxChars && $line !== '') {
                $lines[] = trim($line);
                $line    = $word;
            } else {
                $line .= ($line === '' ? '' : ' ') . $word;
            }
        }

        if ($line !== '') {
            $lines[] = trim($line);
        }

        return $lines ?: [$text];
    }

    // -------------------------------------------------------------------------
    // Data definitions
    // -------------------------------------------------------------------------

    private function categoryData(): array
    {
        return [
            'Clear Aligners' => [
                'description' => 'Removable clear aligner treatments for tooth movement.',
                'subcategories' => [
                    'Full Treatment' => [
                        [
                            'name'        => 'OrthoBrain Clear Aligner – Full Treatment',
                            'price'       => 2999.00,
                            'revisions'   => 3,
                            'term_months' => 18,
                            'from_step'   => 1,
                            'to_step'     => 40,
                            'description' => 'Comprehensive clear aligner treatment covering all stages from start to finish.',
                            'color'       => '#3B82F6',
                        ],
                        [
                            'name'        => 'OrthoBrain Clear Aligner – Lite',
                            'price'       => 1799.00,
                            'revisions'   => 2,
                            'term_months' => 12,
                            'from_step'   => 1,
                            'to_step'     => 20,
                            'description' => 'Shorter treatment for mild to moderate crowding.',
                            'color'       => '#60A5FA',
                        ],
                    ],
                    'Refinements' => [
                        [
                            'name'        => 'Clear Aligner Refinement – Single Round',
                            'price'       => 499.00,
                            'revisions'   => 1,
                            'term_months' => 4,
                            'from_step'   => 1,
                            'to_step'     => 10,
                            'description' => 'One round of refinement aligners to fine-tune final tooth positions.',
                            'color'       => '#93C5FD',
                        ],
                        [
                            'name'        => 'Clear Aligner Refinement – Unlimited',
                            'price'       => 999.00,
                            'revisions'   => null,
                            'term_months' => 24,
                            'description' => 'Unlimited refinement rounds within a 24-month treatment window.',
                            'color'       => '#BFDBFE',
                        ],
                    ],
                    'Retainers' => [
                        [
                            'name'        => 'Clear Retainer – Single Arch',
                            'price'       => 149.00,
                            'term_months' => null,
                            'description' => 'Transparent thermoformed retainer for one arch post-treatment.',
                            'color'       => '#DBEAFE',
                        ],
                        [
                            'name'        => 'Clear Retainer – Both Arches',
                            'price'       => 249.00,
                            'term_months' => null,
                            'description' => 'Set of two thermoformed retainers for upper and lower arches.',
                            'color'       => '#EFF6FF',
                        ],
                    ],
                ],
            ],

            'Braces' => [
                'description' => 'Fixed appliance systems for comprehensive orthodontic correction.',
                'subcategories' => [
                    'Metal Braces' => [
                        [
                            'name'        => 'Standard Metal Braces – Full Treatment',
                            'price'       => 1999.00,
                            'revisions'   => 2,
                            'term_months' => 24,
                            'description' => 'Traditional stainless-steel brackets with wire progression.',
                            'color'       => '#6B7280',
                        ],
                        [
                            'name'        => 'Metal Braces – Phase I (Early Intervention)',
                            'price'       => 1299.00,
                            'revisions'   => 1,
                            'term_months' => 12,
                            'description' => 'Phase I interceptive treatment for growing patients.',
                            'color'       => '#9CA3AF',
                        ],
                    ],
                    'Ceramic Braces' => [
                        [
                            'name'        => 'Ceramic Braces – Full Treatment',
                            'price'       => 2499.00,
                            'revisions'   => 2,
                            'term_months' => 24,
                            'description' => 'Tooth-colored ceramic brackets for a less visible fixed-appliance option.',
                            'color'       => '#F3F4F6',
                        ],
                    ],
                    'Lingual Braces' => [
                        [
                            'name'        => 'Lingual Braces – Full Treatment',
                            'price'       => 3999.00,
                            'revisions'   => 2,
                            'term_months' => 24,
                            'description' => 'Brackets bonded to the tongue side of teeth — completely hidden.',
                            'color'       => '#E5E7EB',
                        ],
                    ],
                ],
            ],

            'Orthopedic Appliances' => [
                'description' => 'Fixed and removable appliances for jaw and arch development.',
                'subcategories' => [
                    'Expanders' => [
                        [
                            'name'        => 'Rapid Palatal Expander (RPE)',
                            'price'       => 899.00,
                            'term_months' => 6,
                            'description' => 'Skeletal expansion appliance to widen the upper jaw.',
                            'color'       => '#10B981',
                        ],
                        [
                            'name'        => 'Slow Palatal Expander',
                            'price'       => 749.00,
                            'term_months' => 9,
                            'description' => 'Gradual arch development for mild constriction cases.',
                            'color'       => '#34D399',
                        ],
                        [
                            'name'        => 'Mandibular Expander',
                            'price'       => 799.00,
                            'term_months' => 6,
                            'description' => 'Lower arch expansion appliance for transverse correction.',
                            'color'       => '#6EE7B7',
                        ],
                    ],
                    'Functional Appliances' => [
                        [
                            'name'        => 'Twin Block Appliance',
                            'price'       => 1099.00,
                            'term_months' => 12,
                            'description' => 'Functional orthopedic appliance for Class II skeletal correction.',
                            'color'       => '#A7F3D0',
                        ],
                        [
                            'name'        => 'Herbst Appliance',
                            'price'       => 1299.00,
                            'term_months' => 12,
                            'description' => 'Fixed functional appliance to advance the mandible.',
                            'color'       => '#D1FAE5',
                        ],
                    ],
                ],
            ],

            'Retainers' => [
                'description' => 'Post-treatment retention solutions to maintain final tooth position.',
                'subcategories' => [
                    'Removable Retainers' => [
                        [
                            'name'        => 'Hawley Retainer – Single Arch',
                            'price'       => 179.00,
                            'description' => 'Acrylic and wire removable retainer for one arch.',
                            'color'       => '#F59E0B',
                        ],
                        [
                            'name'        => 'Hawley Retainer – Both Arches',
                            'price'       => 299.00,
                            'description' => 'Acrylic and wire removable retainer set for upper and lower arches.',
                            'color'       => '#FBBF24',
                        ],
                    ],
                    'Fixed Retainers' => [
                        [
                            'name'        => 'Fixed Lingual Retainer – Single Arch',
                            'price'       => 249.00,
                            'description' => 'Bonded wire retainer affixed behind front teeth for long-term retention.',
                            'color'       => '#FCD34D',
                        ],
                        [
                            'name'        => 'Fixed Lingual Retainer – Both Arches',
                            'price'       => 449.00,
                            'description' => 'Upper and lower bonded lingual retainers.',
                            'color'       => '#FDE68A',
                        ],
                    ],
                ],
            ],

            'Digital Services' => [
                'description' => 'Software-driven orthodontic planning and monitoring services.',
                'subcategories' => [
                    'Treatment Planning' => [
                        [
                            'name'        => 'OrthoBrain AI Treatment Plan',
                            'price'       => 199.00,
                            'description' => 'AI-powered digital treatment plan with 3D tooth movement simulation.',
                            'color'       => '#8B5CF6',
                        ],
                        [
                            'name'        => 'Complex Case Review',
                            'price'       => 349.00,
                            'description' => 'Expert clinician review for surgical or complex multi-disciplinary cases.',
                            'color'       => '#A78BFA',
                        ],
                    ],
                    'Progress Tracking' => [
                        [
                            'name'        => 'Remote Monitoring – 6 Month',
                            'price'       => 149.00,
                            'term_months' => 6,
                            'description' => 'Photo-based remote progress tracking for 6 months of treatment.',
                            'color'       => '#C4B5FD',
                        ],
                        [
                            'name'        => 'Remote Monitoring – 12 Month',
                            'price'       => 249.00,
                            'term_months' => 12,
                            'description' => 'Photo-based remote progress tracking for 12 months of treatment.',
                            'color'       => '#DDD6FE',
                        ],
                    ],
                    '3D Models' => [
                        [
                            'name'        => 'Digital Study Models',
                            'price'       => 99.00,
                            'description' => 'Digital 3D models captured from intraoral scan for records and planning.',
                            'color'       => '#EDE9FE',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function scannerData(): array
    {
        return [
            [
                'name'        => 'iTero Element 5D',
                'description' => 'Align Technology intraoral scanner with NIRI caries detection and color imaging.',
                'portal_link' => 'https://my.itero.com',
            ],
            [
                'name'        => 'iTero Element 5D Plus',
                'description' => 'Enhanced version of the Element 5D with faster scan speed and improved accuracy.',
                'portal_link' => 'https://my.itero.com',
            ],
            [
                'name'        => '3Shape TRIOS 5',
                'description' => '3Shape flagship intraoral scanner with AI-powered scan assist and caries detection.',
                'portal_link' => 'https://portal.3shape.com',
            ],
            [
                'name'        => '3Shape TRIOS 4',
                'description' => 'Wireless 3Shape scanner with color scanning and sleep mode tip hygiene.',
                'portal_link' => 'https://portal.3shape.com',
            ],
            [
                'name'        => 'Medit i700',
                'description' => 'High-speed Medit intraoral scanner with open STL output and free software.',
                'portal_link' => 'https://meditlink.com',
            ],
            [
                'name'        => 'Medit i700 Wireless',
                'description' => 'Cordless version of the Medit i700 for greater chairside freedom.',
                'portal_link' => 'https://meditlink.com',
            ],
            [
                'name'        => 'Carestream CS 3600',
                'description' => 'Carestream Dental intraoral scanner with fast full-arch capture.',
                'portal_link' => null,
            ],
            [
                'name'        => 'Dentsply Sirona Primescan',
                'description' => 'Premium Dentsply Sirona scanner with deep scan technology for full-arch accuracy.',
                'portal_link' => null,
            ],
            [
                'name'        => 'Planmeca PlanScan',
                'description' => 'Open-architecture Planmeca scanner with E4D design software integration.',
                'portal_link' => null,
            ],
            [
                'name'        => 'Dental Wings DWIO',
                'description' => 'Affordable open-format intraoral scanner from Dental Wings / Straumann Group.',
                'portal_link' => null,
            ],
        ];
    }
}
