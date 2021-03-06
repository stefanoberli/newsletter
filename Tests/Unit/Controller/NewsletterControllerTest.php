<?php

namespace Ecodev\Newsletter\Tests\Unit\Controller;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2015
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 * Test case for class Ecodev\Newsletter\Controller\NewsletterController.
 *
 */
class NewsletterControllerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \Ecodev\Newsletter\Controller\NewsletterController
     */
    protected $subject = null;

    protected function setUp()
    {
        $this->subject = $this->getMock('Ecodev\\Newsletter\\Controller\\NewsletterController', array('redirect', 'forward', 'addFlashMessage', 'translate', 'flushFlashMessages'), array(), '', false);
    }

    protected function tearDown()
    {
        unset($this->subject);
    }

    /**
     * @test
     */
    public function listActionFetchesAllNewslettersFromRepositoryAndAssignsThemToView()
    {
        $allNewsletters = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array(), array(), '', false);

        $newsletterRepository = $this->getMock('Ecodev\\Newsletter\\Domain\\Repository\\NewsletterRepository', array('findAll'), array(), '', false);
        $newsletterRepository->expects($this->once())->method('findAll')->will($this->returnValue($allNewsletters));
        $this->inject($this->subject, 'newsletterRepository', $newsletterRepository);

        $view = $this->getMock('Ecodev\\Newsletter\\MVC\\View\\ExtDirectView', array('assign'));
        $view->expects($this->at(0))->method('assign')->with('total', count($allNewsletters));
        $view->expects($this->at(1))->method('assign')->with('data', $allNewsletters);
        $view->expects($this->at(2))->method('assign')->with('success', true);
        $this->inject($this->subject, 'view', $view);

        $this->subject->listAction();
    }

    /**
     * @test
     */
    public function createActionAddsTheGivenNewsletterToNewsletterRepository()
    {
        $newsletter = $this->getMock('Ecodev\\Newsletter\\Domain\Model\\Newsletter', array('getValidatedContent'), array(), '', false);
        $newsletter->expects($this->once())->method('getValidatedContent')->will($this->returnValue(array(
                    'content' => 'some content',
                    'errors' => array(),
                    'warnings' => array(),
                    'infos' => array(),
        )));
        $recipientList = new \Ecodev\Newsletter\Domain\Model\RecipientList\CsvList();
        $newsletter->setRecipientList($recipientList);

        $persistenceManager = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager', array('persistAll'), array(), '', false);
        $persistenceManager->expects($this->once())->method('persistAll')->will($this->returnValue(null));
        $this->inject($this->subject, 'persistenceManager', $persistenceManager);

        $view = $this->getMock('Ecodev\\Newsletter\\MVC\\View\\ExtDirectView');
        $this->inject($this->subject, 'view', $view);

        $newsletterRepository = $this->getMock('Ecodev\\Newsletter\\Domain\\Repository\\NewsletterRepository', array('add'), array(), '', false);
        $newsletterRepository->expects($this->once())->method('add')->with($newsletter);
        $this->inject($this->subject, 'newsletterRepository', $newsletterRepository);

        $this->subject->createAction($newsletter);
    }
}
