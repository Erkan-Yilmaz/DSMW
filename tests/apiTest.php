<?php

define( 'MEDIAWIKI', true );
if( defined( 'MW_INSTALL_PATH' ) ) {
    $IP = MW_INSTALL_PATH;
} else {
    $IP = dirname('../../../.');
}

require_once 'p2pBot.php';
require_once 'BasicBot.php';
require_once '../logootEngine/LogootId.php';
require_once '../logootEngine/LogootPosition.php';
require_once '../logootop/LogootOp.php';
require_once '../logootop/LogootIns.php';
require_once '../logootop/LogootDel.php';
include_once 'p2pAssert.php';
require_once '../p2pExtension.php';
require_once '../patch/Patch.php';
require_once '../files/utils.php';
$wgAutoloadClasses['LogootId'] = "$wgP2PExtensionIP/logootEngine/LogootId.php";

/**
 * apiQueryChangeSet and apiQueryPatch tests
 *
 * @author hantz
 */
class apiTest extends PHPUnit_Framework_TestCase {
    var $p2pBot1;
    var $p2pBot2;
    var $p2pBot3;
    var $tmpServerName;
    var $tmpScriptPath;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp() {
        exec('./initWikiTest.sh');
        exec('rm cache/*');
        $basicbot1 = new BasicBot();
        $basicbot1->wikiServer = 'http://localhost/wiki1';
        $this->p2pBot1 = new p2pBot($basicbot1);

        $basicbot2 = new BasicBot();
        $basicbot2->wikiServer = 'http://localhost/wiki2';
        $this->p2pBot2 = new p2pBot($basicbot2);

        $basicbot3 = new BasicBot();
        $basicbot3->wikiServer = 'http://localhost/wiki3';
        $this->p2pBot3 = new p2pBot($basicbot3);
    }

    /**
     * @access protected
     */
    protected function tearDown() {

    }

    /**
     * test ApiQueryPatch
     */
    function testGetPatch() {

        $patchName = 'Patch:localhost/wiki1';
        $content = '[[patchID::'.$patchName.']] [[onPage::Berlin]] [[previous::localhost/wiki0]]
        [[hasOperation::Localhost/wiki111;Insert;(15555995255933583146:900c17ebee311fb6dd00970d26727577) ;content page berlin]]';
        $this->assertTrue($this->p2pBot1->createPage($patchName,$content),
            'failed to create page '.$patchName.' ('.$this->p2pBot1->bot->results.')');

        $this->assertTrue($this->p2pBot1->createPage($patchName,$content),
            'failed to create page '.$patchName.' ('.$this->p2pBot1->bot->results.')');
        $patchName = 'Patch:localhost/wiki2';
        $content = '[[patchID::'.$patchName.']] [[onPage::Paris]] [[previous::none]]
        [[hasOperation::Localhost/wiki121;Insert;(15555995255933583146:900c17ebee311fb6dd00970d26727577) ;content page Paris]]';

        $this->assertTrue($this->p2pBot1->createPage($patchName,$content),
            'failed to create page '.$patchName.' ('.$this->p2pBot1->bot->results.')');


        //ApiQueryPatch call
        $patchXML = file_get_contents($this->p2pBot1->bot->wikiServer.'/api.php?action=query&meta=patch&papatchId=Patch:localhost/wiki2&format=xml');

        $dom = new DOMDocument();
        $dom->loadXML($patchXML);
        $patchs = $dom->getElementsByTagName('patch');

        foreach($patchs as $p) {
            $this->assertEquals('patch:localhost/wiki2', strtolower($p->getAttribute('id')));
            $this->assertEquals('paris', strtolower($p->getAttribute('onPage')));
            $this->assertEquals('none', strtolower(substr($p->getAttribute('previous'),0,-1)));
        }

        $listeOp = $dom->getElementsByTagName('operation');

        $op = null;
        foreach($listeOp as $o)
            $op[] = $o->firstChild->nodeValue;

        $this->assertTrue(count($op)==1);

        $contentOp = str_replace(" ", "",'Localhost/wiki121; Insert; (15555995255933583146:900c17ebee311fb6dd00970d26727577); content page Paris');
        $this->assertEquals($contentOp,str_replace(" ","", $op[0]));


/*        $patchName = 'Patch:localhost/wiki4';
        $content = '[[patchID::'.$pageName.']] [[onPage::Paris]] [[previous::none]]
        [[hasOperation::Localhost/wiki121;Insert;(15555995255933583146:900c17ebee311fb6dd00970d26727577) ;content page Paris]]';
        $this->assertTrue($this->p2pBot1->createPage($this->p2pBot1->createPage($patchName,$content),
            'failed to create page '.$patchName.' ('.$this->p2pBot1->bot->results.')'));

        //ApiQueryPatch call
        $patchXML = file_get_contents($this->p2pBot1->bot->wikiServer.'/api.php?action=query&meta=patch&papatchId=Patch:Localhost/wiki4&format=xml');

        $dom = new DOMDocument();
        $dom->loadXML($patchXML);
        $patchs = $dom->getElementsByTagName('patch');

        foreach($patchs as $p) {
            $a = $p->getAttribute('id');
            $this->assertEquals('Patch:Localhost/wiki4', $p->getAttribute('id'));
            $a = $p->getAttribute('onPage');
            $this->assertEquals('Paris', $p->getAttribute('onPage'));
            $a = $p->getAttribute('previous');
            $this->assertEquals('None', substr($p->getAttribute('previous'),0,-1));
        }

        $listeOp = $dom->getElementsByTagName('operation');
        $op = null;
        foreach($listeOp as $o)
            $op[] = $o->firstChild->nodeValue;
        $this->assertTrue(count($op)==1);
        $this->assertEquals('localhost/wiki121;insert;(15555995255933583146:900c17ebee311fb6dd00970d26727577);contentpageparis',strtolower(str_replace(' ','',$op[0])));*/

    }

    /**
     * test ApiQueryChangeSet whithout previous changeSet
     */
    public function testGetChangeSetWhithoutPreviousCS() {
        /* test with no previousChangeSet */
        $pageName = "ChangeSet:localhost/wiki12";
        $content='ChangeSet:
changeSetID: [[changeSetID::localhost/wiki12]]
inPushFeed: [[inPushFeed::PushFeed:PushCity]]
previousChangeSet: [[previousChangeSet::none]]
 hasPatch: [[hasPatch::"Patch:Berlin1"]] hasPatch: [[hasPatch::"Patch:Paris0"]]';
        $this->p2pBot1->createPage($pageName, $content);

        $pageName = 'PushFeed:PushCity';
        $content = 'PushFeed:
Name: [[name::CityPush2]]
hasSemanticQuery: [[hasSemanticQuery::-5B-5BCategory:city-5D-5D]]
Pages concerned:
{{#ask: [[Category:city]]}} hasPushHead: [[hasPushHead::ChangeSet:localhost/mediawiki12]]';
        $this->p2pBot1->createPage($pageName,$content);

        //apiQueryChangeSet call
        $cs = file_get_contents($this->p2pBot1->bot->wikiServer.'/api.php?action=query&meta=changeSet&cspushName=PushCity&cschangeSet=none&format=xml');

        $dom = new DOMDocument();
        $dom->loadXML($cs);
        $changeSet = $dom->getElementsByTagName('changeSet');
        foreach($changeSet as $cs) {
            if ($cs->hasAttribute("id")) {
                $CSID = $cs->getAttribute('id');
            }
        }

        $this->assertEquals('localhost/wiki12',strtolower($CSID));

        $listePatch = $dom->getElementsByTagName('patch');

        foreach($listePatch as $pays)
            $patch[] = $pays->firstChild->nodeValue;

        $this->assertTrue(count($patch)==2);
        $this->assertEquals('Patch:Berlin1',$patch[0]);
        $this->assertEquals('Patch:Paris0',$patch[1]);
    }

    /**
     * test apiQueryChangeSet with a previous changeSet
     */
    public function testGetChangeSetWhithPreviousCS() {
        $pageName = "ChangeSet:localhost/wiki13";
        $content='ChangeSet:
changeSetID: [[changeSetID::localhost/wiki13]]
inPushFeed: [[inPushFeed::PushFeed:PushCity]]
previousChangeSet: [[previousChangeSet::ChangeSet:localhost/wiki12]]
 hasPatch: [[hasPatch::"Patch:Berlin2"]]';
        $this->p2pBot1->createPage($pageName, $content);

        //apiQueryChangeSet call
        $cs = file_get_contents($this->p2pBot1->bot->wikiServer.'/api.php?action=query&meta=changeSet&cspushName=PushCity&cschangeSet=ChangeSet:localhost/wiki12&format=xml');

        $dom = new DOMDocument();
        $dom->loadXML($cs);

        $changeSet = $dom->getElementsByTagName('changeSet');
        foreach($changeSet as $cs) {
            if ($cs->hasAttribute("id")) {
                $CSID = $cs->getAttribute('id');
            }
        }

        $this->assertEquals('localhost/wiki13',strtolower($CSID));

        $listePatch = $dom->getElementsByTagName('patch');

        $patch = null;
        foreach($listePatch as $pays)
            $patch[] = $pays->firstChild->nodeValue;

        $this->assertTrue(count($patch)==1);
        $this->assertEquals('Patch:Berlin2',$patch[0]);
    }

    /**
     * test apiQueryChangeSet with an unexist changeSet
     */
    public function testGetChangeSetWhithUnexistCS() {
        $cs = file_get_contents($this->p2pBot1->bot->wikiServer.'/api.php?action=query&meta=changeSet&cspushName=PushCity&cschangeSet=ChangeSet:localhost/wiki13&format=xml');

        $dom = new DOMDocument();
        $dom->loadXML($cs);
        $changeSet = $dom->getElementsByTagName('changeSet');
        $CSID = null;
        foreach($changeSet as $cs) {
            if ($cs->hasAttribute("id")) {
                $CSID = $cs->getAttribute('id');
            }
        }

        $this->assertEquals(null, $CSID);

        $patch = null;
        $listePatch = $dom->getElementsByTagName('patch');
        foreach($listePatch as $pays)
            $patch[] = $pays->firstChild->nodeValue;
        $this->assertEquals(null, $patch);
    }
}
?>
