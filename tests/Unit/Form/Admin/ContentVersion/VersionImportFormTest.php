<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\Form\Admin\ContentVersion;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Validator\ContentZipFile;
use Softspring\CmsDataPlugin\Form\Admin\ContentVersion\VersionImportForm;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class VersionImportFormTest extends TestCase
{
    public function testItConfiguresDefaults(): void
    {
        $resolver = new OptionsResolver();
        $resolver->setDefault('label_format', null);

        (new VersionImportForm())->configureOptions($resolver);

        $options = $resolver->resolve([
            'content_config' => ['_id' => 'pages'],
        ]);

        self::assertSame(['Default', 'import_version'], $options['validation_groups']);
        self::assertSame('sfs_cms_contents', $options['translation_domain']);
        self::assertSame('admin_pages.import_version.form.%name%.label', $options['label_format']);
    }

    public function testItAddsARequiredZipFileField(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())
            ->method('add')
            ->with('file', FileType::class, self::callback(function (array $options): bool {
                return 2 === count($options['constraints'])
                    && $options['constraints'][0] instanceof NotBlank
                    && $options['constraints'][1] instanceof ContentZipFile;
            }))
            ->willReturnSelf();

        (new VersionImportForm())->buildForm($builder, []);
    }
}
