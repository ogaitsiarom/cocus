<?php

namespace App\Tests\Unit;

use App\Note\Dto\UpdateNoteDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateNoteDtoTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testUpdateNoteDtoWithValidData(): void
    {
        $dto = new UpdateNoteDto('Valid Title', 'Valid content for the update.');

        $errors = $this->validator->validate($dto);

        self::assertCount(0, $errors);
        self::assertEquals('Valid Title', $dto->getTitle());
        self::assertEquals('Valid content for the update.', $dto->getContent());
    }

    public function testUpdateNoteDtoWithBlankTitle(): void
    {
        $dto = new UpdateNoteDto('', 'Valid content for the update.');
        $errors = $this->validator->validate($dto);

        self::assertCount(2, $errors);
        $messages = array_map(static fn($violation) => $violation->getMessage(), iterator_to_array($errors));
        self::assertContains('The title cannot be empty', $messages);
        self::assertContains('Your title must be at least 5 characters long', $messages);
    }

    public function testUpdateNoteDtoWithShortTitle(): void
    {
        $dto = new UpdateNoteDto('1234', 'Valid content for the update.');
        $errors = $this->validator->validate($dto);

        self::assertCount(1, $errors);
        self::assertEquals('Your title must be at least 5 characters long', $errors[0]->getMessage());
    }

    public function testUpdateNoteDtoWithTooLongTitle(): void
    {
        $longTitle = str_repeat('a', 256);
        $dto = new UpdateNoteDto($longTitle, 'Valid content for the update.');
        $errors = $this->validator->validate($dto);

        self::assertCount(1, $errors);
        self::assertEquals('Your title cannot be longer than 255 characters', $errors[0]->getMessage());
    }

    public function testUpdateNoteDtoWithBlankContent(): void
    {
        $dto = new UpdateNoteDto('Valid Title', '');
        $errors = $this->validator->validate($dto);

        self::assertCount(2, $errors);
        $messages = array_map(static fn($violation) => $violation->getMessage(), iterator_to_array($errors));
        self::assertContains('The content cannot be empty', $messages);
        self::assertContains('Your content must be at least 5 characters long', $messages);
    }

    public function testUpdateNoteDtoWithShortContent(): void
    {
        $dto = new UpdateNoteDto('Valid Title', '1234');
        $errors = $this->validator->validate($dto);

        self::assertCount(1, $errors);
        self::assertEquals('Your content must be at least 5 characters long', $errors[0]->getMessage());
    }

    public function testUpdateNoteDtoWithTooLongContent(): void
    {
        $longContent = str_repeat('b', 256);
        $dto = new UpdateNoteDto('Valid Title', $longContent);
        $errors = $this->validator->validate($dto);

        self::assertCount(1, $errors);
        self::assertEquals('Your content cannot be longer than 255 characters', $errors[0]->getMessage());
    }
}