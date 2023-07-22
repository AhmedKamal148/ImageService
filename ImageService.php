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
        return (time() + mt_rand(10, 100000) + mt_rand(10, 100000)) . '_' . $path . '.' . $image->getClientOriginalExtension();
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


    /**
     * @throws ValidationException
     */
    public function updateImage($requestImage, $path, $modelImage)
    {
        if ($this->isRequestDataValid($requestImage)) {
            $this->deleteModelImage($modelImage, $path);
            return $this->storeImage($requestImage, $path);

        } else {
            if (!$this->isImageExistInModelDirectory(basename($modelImage), $path)) {
                return $this->storeImage($requestImage, $path);
            }
        }
    }

    public function deleteModelImage($modelImage, $path): bool
    {
        if (!$this->isImage($modelImage)) {

            if (is_null($modelImage)) {
                return true;
            } elseif (!(is_file($modelImage))) {
                return true;
            }
        } else {
            if (!$this->isImageExistInModelDirectory($modelImage, $path)) {
                return true;
            }
            return unlink($this->getImagePath($modelImage, $path));
        }
    }


    private function isImageExistInModelDirectory($image, $path): bool
    {
        return file_exists($this->getImagePath($image, $path));
    }

    private function getImagePath($image, $path): string
    {
        return public_path('images' . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . basename($image));
    }


    private function isRequestDataValid($image): bool
    {
        return $this->checkIsImageValid($image);
    }

    private function checkIsImageValid($image): bool
    {
        return $this->isImage($image);
    }


    public function isImage(mixed $image): bool
    {
        if (is_file($image)) {
            return in_array($this->getRequestImageExtension($image), $this->imageExtensions());
        } else {
            return in_array($this->getModelImageExtension($image), $this->imageExtensions());
        }
    }

    private function imageExtensions(): array
    {
        return ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'];
    }


    private function getRequestImageExtension($image): string
    {
        return $image->getClientOriginalExtension();
    }

    private function getModelImageExtension($modelImage): string
    {
        $extension = substr($modelImage, -4);
        return trim($extension, '.');
    }




}
