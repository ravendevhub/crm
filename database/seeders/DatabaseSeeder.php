<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Modules\CRM\Models\PipelineStage;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\FollowUpTask;
use Modules\CRM\Models\Quotation;
use Modules\CRM\Models\QuotationItem;
use Modules\CRM\Models\CustomerHistory;
use Modules\CRM\Models\Activity;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Permissions
        $permissions = [
            'view_dashboard',
            'view_customers',
            'create_customers',
            'edit_customers',
            'delete_customers',
            'view_leads',
            'create_leads',
            'edit_leads',
            'delete_leads',
            'manage_pipeline',
            'view_quotations',
            'create_quotations',
            'edit_quotations',
            'view_reports',
            // User & Role Management permissions
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'view_roles',
            'manage_roles',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // 2. Create Roles
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $ownerRole = Role::firstOrCreate(['name' => 'Company Owner', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $salesRole = Role::firstOrCreate(['name' => 'Sales', 'guard_name' => 'web']);
        $cashierRole = Role::firstOrCreate(['name' => 'Cashier', 'guard_name' => 'web']);
        $warehouseRole = Role::firstOrCreate(['name' => 'Warehouse', 'guard_name' => 'web']);

        // 3. Map Permissions to Roles
        $ownerRole->syncPermissions($permissions); // All permissions

        $managerRole->syncPermissions([
            'view_dashboard',
            'view_customers', 'create_customers', 'edit_customers',
            'view_leads', 'create_leads', 'edit_leads', 'delete_leads',
            'manage_pipeline',
            'view_quotations', 'create_quotations', 'edit_quotations',
            'view_reports',
        ]);

        $salesRole->syncPermissions([
            'view_dashboard',
            'view_customers', 'create_customers', 'edit_customers',
            'view_leads', 'create_leads', 'edit_leads',
            'view_quotations', 'create_quotations', 'edit_quotations',
        ]);

        $cashierRole->syncPermissions([
            'view_dashboard',
            'view_customers',
            'view_quotations',
        ]);

        $warehouseRole->syncPermissions([
            'view_dashboard',
        ]);

        // 4. Create Companies (Tenants)
        $acme = Company::firstOrCreate(
            ['slug' => 'acme-corp'],
            ['name' => 'Acme Corp']
        );

        $beta = Company::firstOrCreate(
            ['slug' => 'beta-industries'],
            ['name' => 'Beta Industries']
        );

        // 5. Create Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@crm.local'],
            [
                'name' => 'Global Admin',
                'password' => Hash::make('password'),
                'company_id' => null,
            ]
        );
        $admin->assignRole($superAdminRole);
        $admin->companies()->syncWithoutDetaching([$acme->id, $beta->id]);

        $acmeUser = User::firstOrCreate(
            ['email' => 'user@acme.local'],
            [
                'name' => 'Acme User',
                'password' => Hash::make('password'),
                'company_id' => $acme->id,
            ]
        );
        $acmeUser->assignRole($ownerRole);
        $acmeUser->companies()->syncWithoutDetaching([$acme->id]);

        $betaUser = User::firstOrCreate(
            ['email' => 'user@beta.local'],
            [
                'name' => 'Beta User',
                'password' => Hash::make('password'),
                'company_id' => $beta->id,
            ]
        );
        $betaUser->assignRole($ownerRole);
        $betaUser->companies()->syncWithoutDetaching([$beta->id]);

        $acmeSales = User::firstOrCreate(
            ['email' => 'sales@acme.local'],
            [
                'name' => 'Acme Sales',
                'password' => Hash::make('password'),
                'company_id' => $acme->id,
            ]
        );
        $acmeSales->assignRole($salesRole);
        $acmeSales->companies()->syncWithoutDetaching([$acme->id]);

        $acmeCashier = User::firstOrCreate(
            ['email' => 'cashier@acme.local'],
            [
                'name' => 'Acme Cashier',
                'password' => Hash::make('password'),
                'company_id' => $acme->id,
            ]
        );
        $acmeCashier->assignRole($cashierRole);
        $acmeCashier->companies()->syncWithoutDetaching([$acme->id]);

        // Helper function to seed stages
        $seedStages = function ($companyId) {
            $stages = [
                ['name' => 'New', 'order' => 1, 'color' => '#6b7280'],
                ['name' => 'Contacted', 'order' => 2, 'color' => '#3b82f6'],
                ['name' => 'Qualified', 'order' => 3, 'color' => '#f59e0b'],
                ['name' => 'Proposal Sent', 'order' => 4, 'color' => '#06b6d4'],
                ['name' => 'Won', 'order' => 5, 'color' => '#10b981'],
                ['name' => 'Lost', 'order' => 6, 'color' => '#ef4444'],
            ];
            
            $seededStages = [];
            foreach ($stages as $stage) {
                $seededStages[$stage['name']] = PipelineStage::firstOrCreate(
                    ['company_id' => $companyId, 'name' => $stage['name']],
                    ['order' => $stage['order'], 'color' => $stage['color']]
                );
            }
            return $seededStages;
        };

        // 4. Seed Stages for both companies
        $acmeStages = $seedStages($acme->id);
        $betaStages = $seedStages($beta->id);

        // 5. Seed Acme Corp Data
        $google = Customer::firstOrCreate(
            ['company_id' => $acme->id, 'name' => 'Google'],
            [
                'email' => 'info@google.com',
                'phone' => '111-222-3333',
                'website' => 'https://google.com',
                'status' => 'active',
                'assigned_user_id' => $acmeUser->id,
                'created_by' => $admin->id
            ]
        );
        
        $microsoft = Customer::firstOrCreate(
            ['company_id' => $acme->id, 'name' => 'Microsoft'],
            [
                'email' => 'info@microsoft.com',
                'phone' => '444-555-6666',
                'website' => 'https://microsoft.com',
                'status' => 'active',
                'assigned_user_id' => $acmeUser->id,
                'created_by' => $admin->id
            ]
        );

        $apple = Customer::firstOrCreate(
            ['company_id' => $acme->id, 'name' => 'Apple'],
            [
                'email' => 'info@apple.com',
                'phone' => '777-888-9999',
                'website' => 'https://apple.com',
                'status' => 'lead',
                'assigned_user_id' => $admin->id,
                'created_by' => $admin->id
            ]
        );

        CustomerHistory::create([
            'company_id' => $acme->id,
            'customer_id' => $google->id,
            'event_type' => 'creation',
            'description' => 'Google record created and assigned to Acme User.',
            'created_by' => $admin->id
        ]);

        $deal1 = Lead::firstOrCreate(
            ['company_id' => $acme->id, 'title' => 'Google Workspace Migration'],
            [
                'customer_id' => $google->id,
                'pipeline_stage_id' => $acmeStages['Won']->id,
                'estimated_value' => 15000.00,
                'status' => 'won',
                'assigned_user_id' => $admin->id,
                'created_by' => $admin->id
            ]
        );

        $deal2 = Lead::firstOrCreate(
            ['company_id' => $acme->id, 'title' => 'Azure Enterprise Contract'],
            [
                'customer_id' => $microsoft->id,
                'pipeline_stage_id' => $acmeStages['Proposal Sent']->id,
                'estimated_value' => 45000.00,
                'status' => 'proposal_sent',
                'assigned_user_id' => $acmeUser->id,
                'created_by' => $admin->id
            ]
        );

        $quote1 = Quotation::firstOrCreate(
            ['company_id' => $acme->id, 'quotation_number' => 'QT-ACME-2026-001'],
            [
                'lead_id' => $deal1->id,
                'customer_id' => $google->id,
                'total_amount' => 15000.00,
                'status' => 'accepted',
                'assigned_user_id' => $admin->id,
                'created_by' => $admin->id
            ]
        );

        QuotationItem::firstOrCreate(
            ['company_id' => $acme->id, 'quotation_id' => $quote1->id, 'description' => 'Google Workspace Corporate Tier Subscription'],
            [
                'quantity' => 100,
                'unit_price' => 150.00,
                'discount' => 0.00,
                'tax_rate' => 0.00,
                'total' => 15000.00
            ]
        );

        FollowUpTask::firstOrCreate(
            ['company_id' => $acme->id, 'title' => 'Schedule Azure Specs Call'],
            [
                'related_type' => \Modules\CRM\Models\Lead::class,
                'related_id' => $deal2->id,
                'notes' => 'Coordinate with Satya to detail Azure migration parameters.',
                'due_date' => now()->addDays(3),
                'status' => 'pending',
                'priority' => 'high',
                'assigned_user_id' => $acmeUser->id,
                'created_by' => $admin->id
            ]
        );

        Activity::firstOrCreate(
            ['company_id' => $acme->id, 'description' => 'Discussed final pricing terms for Google Workspace.'],
            [
                'customer_id' => $google->id,
                'lead_id' => $deal1->id,
                'type' => 'call',
                'due_date' => now()->subDays(2),
                'completed_at' => now()->subDays(2),
                'assigned_user_id' => $admin->id,
                'created_by' => $admin->id
            ]
        );


        // 6. Seed Beta Industries Data
        $tesla = Customer::firstOrCreate(
            ['company_id' => $beta->id, 'name' => 'Tesla'],
            [
                'email' => 'info@tesla.com',
                'phone' => '222-333-4444',
                'website' => 'https://tesla.com',
                'status' => 'active',
                'assigned_user_id' => $betaUser->id,
                'created_by' => $admin->id
            ]
        );
        
        $spacex = Customer::firstOrCreate(
            ['company_id' => $beta->id, 'name' => 'SpaceX'],
            [
                'email' => 'info@spacex.com',
                'phone' => '555-666-7777',
                'website' => 'https://spacex.com',
                'status' => 'active',
                'assigned_user_id' => $betaUser->id,
                'created_by' => $admin->id
            ]
        );

        CustomerHistory::create([
            'company_id' => $beta->id,
            'customer_id' => $tesla->id,
            'event_type' => 'creation',
            'description' => 'Tesla record created and assigned to Beta User.',
            'created_by' => $admin->id
        ]);

        $deal3 = Lead::firstOrCreate(
            ['company_id' => $beta->id, 'title' => 'Model 3 Fleet Order'],
            [
                'customer_id' => $tesla->id,
                'pipeline_stage_id' => $betaStages['Won']->id,
                'estimated_value' => 85000.00,
                'status' => 'won',
                'assigned_user_id' => $admin->id,
                'created_by' => $admin->id
            ]
        );

        $deal4 = Lead::firstOrCreate(
            ['company_id' => $beta->id, 'title' => 'Falcon Heavy Satellite Launch'],
            [
                'customer_id' => $spacex->id,
                'pipeline_stage_id' => $betaStages['Proposal Sent']->id,
                'estimated_value' => 250000.00,
                'status' => 'proposal_sent',
                'assigned_user_id' => $betaUser->id,
                'created_by' => $admin->id
            ]
        );

        $quote2 = Quotation::firstOrCreate(
            ['company_id' => $beta->id, 'quotation_number' => 'QT-BETA-2026-001'],
            [
                'lead_id' => $deal3->id,
                'customer_id' => $tesla->id,
                'total_amount' => 85000.00,
                'status' => 'accepted',
                'assigned_user_id' => $admin->id,
                'created_by' => $admin->id
            ]
        );

        QuotationItem::firstOrCreate(
            ['company_id' => $beta->id, 'quotation_id' => $quote2->id, 'description' => 'Industrial Battery Pack Storage Assembly'],
            [
                'quantity' => 2,
                'unit_price' => 42500.00,
                'discount' => 0.00,
                'tax_rate' => 0.00,
                'total' => 85000.00
            ]
        );

        FollowUpTask::firstOrCreate(
            ['company_id' => $beta->id, 'title' => 'Payload spec integration review'],
            [
                'related_type' => \Modules\CRM\Models\Lead::class,
                'related_id' => $deal4->id,
                'notes' => 'Review SpaceX heavy carrier specification sheet.',
                'due_date' => now()->addDays(5),
                'status' => 'pending',
                'priority' => 'medium',
                'assigned_user_id' => $betaUser->id,
                'created_by' => $admin->id
            ]
        );

        Activity::firstOrCreate(
            ['company_id' => $beta->id, 'description' => 'Sent Model 3 fleet pricing and invoice details.'],
            [
                'customer_id' => $tesla->id,
                'lead_id' => $deal3->id,
                'type' => 'email',
                'due_date' => now()->subDay(),
                'completed_at' => now()->subDay(),
                'assigned_user_id' => $admin->id,
                'created_by' => $admin->id
            ]
        );
    }
}
