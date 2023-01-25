<?php

namespace App\Services;


use Illuminate\Validation\ValidationException;

class ImageService
{

    private string $fieldNameFromRequest = 'image';

    /**
     * @throws ValidationException
     */
    public function storeImage($image, $path): string
    {
        if ($this->checkIsImageValid($image)) {

            $imageName = $this->getImageName($image, $path);

            $this->saveImageToModelDirectory($image, $path, $imageName);

            return $imageName;

        } else {
            throw ValidationException::withMessages([$this->fieldNameFromRequest => 'This Field Must Be An Image']);
        }

    }

    private function getImageName($image, $path): string
    {
        return time() . '_' . $path . '.' . $image->getClientOriginalExtension();
    }

    private function saveImageToModelDirectory($image, $path, $imageName): void
    {
        $image->move(public_path('images' . DIRECTORY_SEPARATOR . $path), $imageName);
    }

    public function setFieldName($fieldNameFromRequest): static
    {
        $this->fieldNameFromRequest = $fieldNameFromRequest;

        return $this;
    }

    public function updateImage($requestImage, $path, $modelImage): string
    {

        if ($this->isRequestDataValid($requestImage, $modelImage) && $this->deleteImage($modelImage, $path)) {

            return $this->storeImage($requestImage, $path);

        } else {
            throw ValidationException::withMessages([$this->fieldNameFromRequest => 'The Model Image Doen\'t Found At Model Directory']);
        }

    }

    public function deleteImage($image, $path): bool
    {

        if ($this->isImageExistInModelDirectory($image, $path)) {

            return $this->unLinkImage($image, $path);
        } else {
            throw ValidationException::withMessages(['error', 'This Image Is Not Valid']);
        }
    }

    private function isRequestDataValid($image, $modelImage): bool
    {
        return $this->isImageNotNull($image)
            && $this->isImageNotNull($modelImage)
            && $this->isImage($image)
            && $this->isImage($modelImage);
    }

    private function isImageExistInModelDirectory($image, $path): bool
    {
        return file_exists($this->getImagePath($image, $path));
    }

    private function getImagePath($image, $path): string
    {
        return public_path('images' . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $image);
    }

    private function unLinkImage($image, $path): bool
    {
        return unlink($this->getImagePath($image, $path));
    }

    private function checkIsImageValid($image): bool
    {
        return $this->isImage($image) && $this->isImageNotNull($image);
    }

    private function isImageNotNull($image): bool
    {
        return !(is_null($image));
    }


    private function isImage($image): bool
    {
        return in_array($this->getImageExtension($image), $this->imageExtensions());
    }

    private function getImageExtension($image): string
    {
        return $image->getClientOriginalExtension();
    }

    private function imageExtensions(): array
    {
        return ['jpg', 'jpeg', 'png', 'webp'];
    }


}
