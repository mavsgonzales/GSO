<?php
declare(strict_types=1);

namespace App\Config;

class Constants
{
    public const STATUS_CHOICES = [
        'Serviceable',
        'Unserviceable',
        'Under Repair',
        'Surrendered',
        'Disposed',
        'For Condemnation',
        'Transferred',
        'Lost / Missing'
    ];

    public const UNIT_CHOICES = [
        'unit',
        'piece',
        'set',
        'lot',
        'box',
        'roll',
        'bundle',
        'ream',
        'meters',
        'linear meters',
        'sq. meters',
        'pairs',
        'drum',
        'cylinder',
        'package'
    ];

    public const DEPARTMENT_CHOICES = [
        'Office Of The Governor',
        'Office Of The Vice Governor',
        'Provincial Administrator',
        'Office Of The Executive Assistant',
        'Provincial Legal Office',
        'Provincial Planning & Development Office',
        'Provincial Disaster Risk Reduction and Management Office',
        'Bids and Awards Committee',
        'Provincial Human Resource Management Office',
        'Provincial Youth Development Office',
        'Provincial Tourism Office',
        'Public Employment Service Office',
        'Provincial Social Welfare & Development Office',
        'Persons with Disability Affairs Office',
        'Public Affairs Information Assistance Division',
        'Provincial Accounting Office',
        'Office Of The Provincial Agriculturist',
        'Provincial Assessors Office',
        'Provincial Budget Office',
        'Provincial Health Office',
        'Aurora Memorial Hospital',
        'Casiguran District Hospital',
        'Dingalan Community Hospital',
        'Maria Aurora Community Hospital',
        'Provincial Treasury Office',
        'Provincial Veterinary Office',
        'Office of the Sangguniang Panlalawigan Secretariat',
        'Provincial Nutrition Action Office',
        'Occupational Health Clinic',
        'Provincial Government Employees Association',
        'Provincial Equipment Pool Office',
        'Provincial Internal Audit Services Office',
        'Provincial Cooperatives Development Office',
        'Provincial Engineering Office',
        'Environment and Natural Resources Office',
        'Provincial General Services Office',
        'Provincial Warden’s Division',
        'Provincial Local Economic Development and Investment Promotion Office',
        'Provincial Population Office',
        'Cultural Affairs Division',
        'Provincial Agricultural and Biosystems Engineering Division'
    ];

    public const SOURCE_OF_FUND_CHOICES = [
        'General Fund (GF)',
        '20% Component Development Fund (CDF)',
        'Special Education Fund (SEF)',
        'Local Disaster Risk Reduction & Management Fund (LDRRMF)',
        'Trust Fund',
        'Calamity Fund',
        'Capital Outlay (CO)',
        'National Government Subsidy / Grant',
        'Official Development Assistance (ODA)',
        'Loan Proceeds',
        'Donations'
    ];

    public const GENERAL_LEDGER_CHOICES = [
        '1-07-01-010 Land',
        '1-07-02-010 Land Improvements',
        '1-07-03-010 Infrastructure Assets - Road Networks',
        '1-07-03-020 Infrastructure Assets - Flood Control Systems',
        '1-07-03-030 Infrastructure Assets - Sewer Systems',
        '1-07-03-040 Infrastructure Assets - Water Supply Systems',
        '1-07-04-010 Buildings & Other Structures',
        '1-07-05-010 Machinery & Heavy Equipment',
        '1-07-05-020 Office Equipment',
        '1-07-05-030 Information & Communication Technology (ICT) Equipment',
        '1-07-05-090 Disaster Response and Rescue Equipment',
        '1-07-05-100 Military, Police & Security Equipment',
        '1-07-05-110 Medical Equipment',
        '1-07-05-130 Sports Equipment',
        '1-07-05-140 Technical & Scientific Equipment',
        '1-07-05-990 Other Machinery and Equipment',
        '1-07-06-010 Motor Vehicles',
        '1-07-07-010 Furniture and Fixtures',
        '1-07-08-010 Books',
        '1-07-99-990 Other Property, Plant & Equipment'
    ];

    public const INVENTORY_FIELDS = [
        'barcode' => 'Barcode',
        'property_number' => 'Property Number',
        'article_road_name' => 'Article / Road Name',
        'description' => 'Description',
        'onhand_quantity' => 'On-hand Qty',
        'unit' => 'Unit',
        'unit_cost' => 'Unit Cost',
        'onhand_total_value' => 'Total Value',
        'status' => 'Status',
        'location' => 'Location',
        'accountable' => 'Accountable',
        'designation' => 'Designation',
        'department' => 'Department',
        'previously_accountable' => 'Previously Accountable',
        'acquisition_date' => 'Acquisition Date',
        'date_surrendered' => 'Date Surrendered',
        'po_number' => 'PO Number',
        'pr_number' => 'PR Number',
        'invoice_number' => 'Invoice Number',
        'are_number' => 'ICS Number',
        'air_number' => 'AIR Number',
        'remarks' => 'Remarks',
        'supplier' => 'Supplier',
        'source_of_fund' => 'Source of Fund',
        'general_ledger' => 'General Ledger',
    ];
}
