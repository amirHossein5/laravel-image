<?php

namespace AmirHossein5\LaravelImage\Tests\Feature;

use AmirHossein5\LaravelImage\Facades\Image;
use AmirHossein5\LaravelImage\Tests\TestCase;
use LogicException;

class TransactionTest extends TestCase
{
    public function test_zero_max_attempt_throws_exception()
    {
        $this->expectException(LogicException::class);
        Image::transaction(function () {
            Image::raw($this->image)->in()->save();
        }, 0);
    }

    public function test_transaction_method_when_throws_exception()
    {
        $image1 = $image2 = null;

        Image::raw($this->image)->in('')->be('not-delete.png')->save();

        try {
            Image::transaction(function () use (&$image1, &$image2) {
                $image1 = Image::make($this->image)
                    ->setExclusiveDirectory('post')
                    ->disk('storage')
                    ->setSizesDirectory('not-create')
                    ->save(false, function ($image) {
                        return [
                            'index'          => $image->imagePath,
                            'directory'      => $image->imageDirectory,
                        ];
                    });

                $image2 = Image::raw($this->image)
                    ->in('')
                    ->be('not-delete.png')
                    ->replace(false, function ($image) {
                        return $image->imagePath;
                    });

                Image::raw($this->image)->disk()->save();
            });
        } catch (\Throwable $e) {
            $this->assertDirectoryDoesNotExist(storage_path('app/'.$image1['directory']));

            foreach ($image1['index'] as $image) {
                $this->assertFileDoesNotExist(storage_path('app/'.$image));
            }

            // $this->assertFileDoesNotExist(public_path($image2));

            $this->assertFileExists(public_path('not-delete.png'));

            return;
        }

        throw new \Exception('transaction did not throw the exception.');
    }

    public function test_transaction_manually_when_throws_exception()
    {
        Image::beginTransaction();

        $image1 = Image::make($this->image)
            ->setExclusiveDirectory(time())
            ->disk('storage')
            ->save(false, function ($image) {
                return [
                    'index'          => $image->imagePath,
                    'directory'      => $image->imageDirectory,
                ];
            });

        $image2 = Image::raw($this->image)
            ->in('')
            ->replace(false, function ($image) {
                return $image->imagePath;
            });

        try {
            Image::raw($this->image)->disk('wrong')->save();

            Image::commit();
        } catch (\Throwable $e) {
            Image::rollBack();
        }

        $this->assertDirectoryDoesNotExist(storage_path('app/'.$image1['directory']));

        foreach ($image1['index'] as $image) {
            $this->assertFileDoesNotExist(storage_path('app/'.$image));
        }

        $this->assertFileDoesNotExist(public_path($image2));
    }

    public function test_transaction_method_when_not_throws_exception_works()
    {
        $image1 = $image2 = null;

        Image::raw($this->image)
            ->in('')
            ->be('logo.png')
            ->save();

        Image::transaction(function () use (&$image1, &$image2) {
            $image1 = Image::make($this->image)
                ->setExclusiveDirectory('post')
                ->disk('storage')
                ->save(false, function ($image) {
                    return [
                        'index'          => $image->imagePath,
                    ];
                });

            $image2 = Image::raw($this->image)
                ->in('')
                ->be('logo.png')
                ->replace(false, function ($image) {
                    return $image->imagePath;
                });
        });

        foreach ($image1['index'] as $image) {
            $this->assertFileExists(storage_path('app/'.$image));
        }

        $this->assertFileExists(public_path($image2));
    }

    public function test_transaction_manually_when_not_throws_exception_works()
    {
        Image::raw($this->image)
            ->in('')
            ->be('logo.png')
            ->save();

        Image::beginTransaction();

        $image1 = Image::make($this->image)
            ->setExclusiveDirectory('post')
            ->disk('storage')
            ->save(false, function ($image) {
                return [
                    'index'          => $image->imagePath,
                ];
            });

        $image2 = Image::raw($this->image)
            ->in('')
            ->be('logo.png')
            ->replace(false, function ($image) {
                return $image->imagePath;
            });

        Image::commit();

        foreach ($image1['index'] as $image) {
            $this->assertFileExists(storage_path('app/'.$image));
        }

        $this->assertFileExists(public_path($image2));
    }

    public function test_manually_transaction_when_commit_and_rollback_methods_are_next_to_each_other()
    {
        Image::beginTransaction();

        $image = Image::raw($this->image)
            ->in('post')
            ->save(false, function ($image) {
                return $image->imagePath;
            });

        // first method will work (commit)
        Image::commit();
        Image::rollBack();

        $this->assertFileExists(public_path($image));

        Image::beginTransaction();

        $image = Image::raw($this->image)
            ->in('post')
            ->save(false, function ($image) {
                return $image->imagePath;
            });

        // first method will work (rollback)
        Image::rollBack();
        Image::commit();

        $this->assertFileDoesNotExist(public_path($image));
    }
}
