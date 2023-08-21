<?php

namespace App\Exports;

use App\Models\EmailContact;
use App\Models\Contact;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class EmailContactExport implements FromView, WithColumnWidths, WithStyles
{

    protected $status;
    protected $groupId;

    public function __construct($status, $groupId = null) {
        $this->status = $status;
        $this->groupId = $groupId;
    }

    public function view(): View
    {
        if($this->status && !$this->groupId){
            $contacts = EmailContact::whereNull('user_id')->select('name', 'email')->get();
        }else if(!$this->status && $this->groupId){ 
            $contacts = EmailContact::where('email_group_id', $this->groupId)->select('name', 'email')->get();
        }else{ 
            $contacts = EmailContact::where('user_id', auth()->user()->id)->select('name', 'email')->get();
        }

        return view('partials.email_contact_excel', [
            'contacts' => $contacts,
        ]);
    }


    public function styles(Worksheet $sheet)
    {
        return [
            'A1' => ['font' => ['bold' => true,'size' => 12,]]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30
        ];
    }
}
