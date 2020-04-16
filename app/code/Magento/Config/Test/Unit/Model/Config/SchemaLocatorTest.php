<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config;

use Magento\Config\Model\Config\SchemaLocator;
use Magento\Framework\Module\Dir\Reader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SchemaLocatorTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_moduleReaderMock;

    /**
     * @var SchemaLocator
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_moduleReaderMock = $this->createMock(Reader::class);

        $this->_moduleReaderMock->expects(
            $this->any()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Config'
        )->will(
            $this->returnValue('schema_dir')
        );
        $this->_model = new SchemaLocator($this->_moduleReaderMock);
    }

    public function testGetSchema()
    {
        $this->assertEquals('schema_dir/system.xsd', $this->_model->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals('schema_dir/system_file.xsd', $this->_model->getPerFileSchema());
    }
}
