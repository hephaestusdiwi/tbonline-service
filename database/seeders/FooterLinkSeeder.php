<?php

namespace Database\Seeders;

use App\Models\FooterLink;
use App\Models\FooterLinkGroup;
use Illuminate\Database\Seeder;

class FooterLinkSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name'       => 'About Us',
                'sort_order' => 0,
                'links'      => [
                    ['label' => 'Our Story',                 'url' => '/about'],
                    ['label' => 'Sustainability Commitment', 'url' => '/sustainability'],
                    ['label' => 'Careers',                   'url' => '/careers'],
                    ['label' => 'Press & Media',             'url' => '/press'],
                ],
            ],
            [
                'name'       => 'Customer Service',
                'sort_order' => 1,
                'links'      => [
                    ['label' => 'Size Guide',          'url' => '/size-guide'],
                    ['label' => 'Shipping Information','url' => '/shipping'],
                    ['label' => 'Returns & Exchanges', 'url' => '/returns'],
                    ['label' => 'FAQs',                'url' => '/faq'],
                    ['label' => 'Contact Us',          'url' => '/contact'],
                ],
            ],
            [
                'name'       => 'Account & Orders',
                'sort_order' => 2,
                'links'      => [
                    ['label' => 'Order Tracking',  'url' => '/orders/track'],
                    ['label' => 'Manage Account',  'url' => '/account'],
                    ['label' => 'Gift Cards',       'url' => '/gift-cards'],
                    ['label' => 'Wishlist',         'url' => '/wishlist'],
                ],
            ],
            [
                'name'       => 'Special Programs',
                'sort_order' => 3,
                'links'      => [
                    ['label' => 'Loyalty Program',   'url' => '/loyalty'],
                    ['label' => 'Student Discount',  'url' => '/student-discount'],
                    ['label' => 'Affiliate Program', 'url' => '/affiliate'],
                ],
            ],
            [
                'name'       => 'Legal & Privacy',
                'sort_order' => 4,
                'links'      => [
                    ['label' => 'Terms & Conditions', 'url' => '/terms'],
                    ['label' => 'Privacy Policy',     'url' => '/privacy'],
                    ['label' => 'Accessibility',      'url' => '/accessibility'],
                ],
            ],
            [
                'name'       => 'Resources',
                'sort_order' => 5,
                'links'      => [
                    ['label' => 'Style Guide',   'url' => '/style-guide'],
                    ['label' => 'Blog',          'url' => '/blog'],
                    ['label' => 'Store Locator', 'url' => '/stores'],
                    ['label' => 'Site Map',      'url' => '/sitemap'],
                ],
            ],
        ];

        foreach ($data as $groupData) {
            $links = $groupData['links'];
            unset($groupData['links']);

            $group = FooterLinkGroup::create($groupData);

            foreach ($links as $i => $link) {
                FooterLink::create([
                    'footer_link_group_id' => $group->id,
                    'label'                => $link['label'],
                    'url'                  => $link['url'],
                    'open_new_tab'         => false,
                    'sort_order'           => $i,
                ]);
            }
        }
    }
}