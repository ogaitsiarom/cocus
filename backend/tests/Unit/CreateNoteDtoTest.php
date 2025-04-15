<?php

namespace App\Tests\Unit;

use App\Note\Dto\CreateNoteDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateNoteDtoTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testCreateNoteDtoWithValidData(): void
    {
        $dto = new CreateNoteDto('Valid Title', 'Valid content for the note.');

        $errors = $this->validator->validate($dto);

        self::assertCount(0, $errors);
        self::assertEquals('Valid Title', $dto->getTitle());
        self::assertEquals('Valid content for the note.', $dto->getContent());
    }

    public function testCreateNoteDtoWithBlankTitle(): void
    {
        $dto = new CreateNoteDto('', 'Valid content for the note.');
        $errors = $this->validator->validate($dto);

        self::assertCount(2, $errors);
        $violationMessages = array_map(static fn($violation) => $violation->getMessage(), iterator_to_array($errors));
        self::assertContains('The title cannot be empty', $violationMessages);
        self::assertContains('Your title must be at least 5 characters long', $violationMessages);
    }

    public function testCreateNoteDtoWithShortTitle(): void
    {
        $dto = new CreateNoteDto('1234', 'Valid content for the note.');
        $errors = $this->validator->validate($dto);

        self::assertCount(1, $errors);
        self::assertEquals('Your title must be at least 5 characters long', $errors[0]->getMessage());
    }

    public function testCreateNoteDtoWithBlankContent(): void
    {
        $dto = new CreateNoteDto('Valid Title', '');
        $errors = $this->validator->validate($dto);

        self::assertCount(2, $errors);
        $violationMessages = array_map(static fn($violation) => $violation->getMessage(), iterator_to_array($errors));
        self::assertContains('The content cannot be empty', $violationMessages);
        self::assertContains('Your content must be at least 5 characters long', $violationMessages);
    }

    public function testCreateNoteDtoWithShortContent(): void
    {
        $dto = new CreateNoteDto('Valid Title', '1234');
        $errors = $this->validator->validate($dto);

        self::assertCount(1, $errors);
        self::assertEquals('Your content must be at least 5 characters long', $errors[0]->getMessage());
    }

    public function testCreateNoteDtoTooLongTitle(): void
    {
        $longTitle = str_repeat('a', 256);
        $dto = new CreateNoteDto($longTitle, 'Valid content for the note.');
        $errors = $this->validator->validate($dto);

        self::assertCount(1, $errors);
        self::assertEquals('Your title cannot be longer than 255 characters', $errors[0]->getMessage());
    }
}