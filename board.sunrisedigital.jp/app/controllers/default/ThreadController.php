<?php
class ThreadController extends Sdx_Controller_Action_Http
{
    public function indexAction()
    {
        Sdx_Debug::dump($this->_getParam('thread_id'), 'title');
    }
    public function addAction()
    {
        
    }
    public function deleteAction()
    {
        
    }
    public function listAction()
    {
        //entryテーブルクラスの取得
        $t_entry = Bd_Orm_Main_Entry::createTable();
        //JOIN予定のAccountテーブルのテーブルクラスを取得
        $t_account = Bd_Orm_Main_Account::createTable();
        $t_thread = Bd_Orm_Main_Thread::createTable();
        //JOIN
        $t_entry->addJoinLeft($t_account);
        $t_entry->addJoinLeft($t_thread);
        //selectを取得
        $select = $t_entry->getSelectWithJoin();
        //ORDER BY thread_id ASC(thread_idを昇順でSELECT)
        $select->order('thread_id ASC');
        //URLから受け取ったID分だけをSelectするよう条件追記
        $select->where("id = $_GET");
         //fetchAll()で全て取得して$entryへ入れておく
        $entry = $t_entry->fetchAll($select);
        //$entryの内容をテンプレに渡す。
        $this->view->assign("entry_list", $entry);
        
        //確認用ダンプ出力。いらなくなったら消す
        Sdx_Debug::dump($entry, "Sdx_Debug::dumpの出力結果");
    }
}
?>
