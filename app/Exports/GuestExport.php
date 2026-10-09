<?php

namespace App\Exports;

use App\Models\Event;
use App\Models\Guest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromCollection;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class GuestExport implements FromCollection, ShouldQueue
{
    /**
    * @return \Illuminate\Support\Collection
    */
    private $eventId;

    function __construct($eventId)
    {
        $this->eventId = $eventId;
    }

    public function collection()
    {
        // $tempDir = storage_path('app/public/files/excel/');
        // if (!file_exists($tempDir)) {
        //     mkdir($tempDir);
        // }

        // $this->guests->each(function ($guest) use ($tempDir) {
        //     $qrCode = QrCode::format('png')->size(200)->generate(route('client.storeQR', $guest->qrcode));
        //     $image = \Intervention\Image\Facades\Image::make($qrCode->toHtml());
        //     $filename = Str::slug($guest->fullname) . '-' . $guest->mssv . '.png';
        //     $imagePath = $tempDir . $filename;
        //     $image->save($imagePath);

        //     $guest->image_path = url('/storage/files/excel/' . $filename); // Thêm đường dẫn hình ảnh vào mô hình Guest
        // });
        $guests = Event::find($this->eventId)->guests;
        return $guests->map(function ($item) {
            $filename = Str::slug($item->fullname) . '-' . $item->mssv . '-' . $this->eventId . '.png';
            return [
                'fullname' => $item->fullname,
                'mssv' => $item->mssv,
                'status' => $item->status === 'JOINED' ? "Đã tham gia" : "Chưa tham gia",
                'image' => '=CONCATENATE("' . url('/storage/files/excel/' . $filename) . '")'
            ];
        });
    }
}
