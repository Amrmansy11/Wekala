<?php

namespace App\Repositories\Vendor;

use App\Models\Offer;
use App\Repositories\BaseRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class OfferRepository extends BaseRepository
{
    protected Model $model;

    public function __construct(Offer $model)
    {
        $this->model = $model;
        parent::__construct($model);
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws Exception
     */
    public function store(array $data, ?UploadedFile $logo = null, ?UploadedFile $cover = null): Offer
    {
        return DB::transaction(function () use ($data, $logo, $cover) {
            $offer = new Offer(array_merge($data, [
                'vendor_id' => $data['vendor_id'],
                'start' => Carbon::parse($data['start']),
                'end' => Carbon::parse($data['end']),
            ]));
            $offer->creatable()->associate(auth()->user());
            $offer->save();

            if (!empty($data['products']) && is_array($data['products'])) {
                $offer->products()->sync($data['products']);
            }

            if ($logo) {
                $offer->addMedia($logo)->toMediaCollection('logo');
            }
            if ($cover) {
                $offer->addMedia($cover)->toMediaCollection('cover');
            }

            return $offer->load('products');
        });
    }

    public function show($id): Offer
    {
        $offer = $this->model->query()->findOrFail($id);
        return $offer->load('products');
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws Exception
     */
    public function update($id, $data, ?UploadedFile $logo = null, ?UploadedFile $cover = null): Offer
    {
        return DB::transaction(function () use ($id, $data, $logo, $cover) {
            $offer = $this->model->query()->findOrFail($id);
            $offer->update(array_merge($data, [
                'vendor_id' => $data['vendor_id'],
                'start' => Carbon::parse($data['start']),
                'end' => Carbon::parse($data['end']),
            ]));

            if (!empty($data['products']) && is_array($data['products'])) {
                $offer->products()->sync($data['products']);
            }

            if ($logo) {
                $offer->clearMediaCollection('logo');
                $offer->addMedia($logo)->toMediaCollection('logo');
            }
            if ($cover) {
                $offer->clearMediaCollection('cover');
                $offer->addMedia($cover)->toMediaCollection('cover');
            }

            return $offer->load('products');
        });
    }

    /**
     * @throws Exception
     */
    public function delete($id): bool
    {
        return DB::transaction(function () use ($id) {
            $offer = $this->model->query()->findOrFail($id);
            $offer->products()->detach();
            $offer->clearMediaCollection('logo');
            $offer->clearMediaCollection('cover');
            return $offer->delete();
        });
    }
}
