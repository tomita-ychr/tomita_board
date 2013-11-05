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
        //URLからthread_idを取得。
        //なお、thread_idはroute.ymlに付けた変数名。
        //$num = $this->_getParam('thread_id');変数を使う必要なくなったのでコメントアウト
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
        $select->add("thread.id", $this->_getParam('thread_id'));
         //fetchAll()で全て取得して$entryへ入れておく
        $entry = $t_entry->fetchAll($select);
        //$entryの内容をテンプレに渡す。
        $this->view->assign("entry_list", $entry);
        
        //確認用ダンプ出力。いらなくなったら消す
        //Sdx_Debug::dump($entry, '$entryの出力結果');
        Sdx_Debug::dump($_SESSION, '$_SESSIONの出力結果');
        
        //コメント投稿関係はこっちのメソッドに任せる。
        $form = $this->formCreation();
        Sdx_Debug::dump($form, '$formの出力結果');

        if(isset($_SESSION['form']))
        {
          $form->bind($_SESSION['form']);
          $form->execValidate();
          unset($_SESSION['form']);
        }
        $this->view->assign('form', $form);
        
        

    }
    private function formCreation()
    {
        $form = new Sdx_Form();//インスタンス作成
        $form
        ->setAction('/thread/'.$this->_getParam('thread_id').'/save-entry') //アクション先を設定
        ->setMethodToPost();     //メソッドをポストに変更
 
        //各エレメントをフォームにセット
        //アカウントID
        $elem = new Sdx_Form_Element_Text();
        $elem
                ->setName('account_id')
                ->addValidator(new Sdx_Validate_NotEmpty('何も入力ないのは寂しいです'))
                ->addValidator(new Sdx_Validate_Regexp('/^[0-9]+$/u','つかえるのは数字だけね'));
        $form->setElement($elem);
        //コメント
        $elem = new Sdx_Form_Element_Textarea();
        $elem
                ->setName('body')
                //とりあえずコメントだけなのでNULL値チェックだけでよいかと。
                ->addValidator(new Sdx_Validate_NotEmpty('何も入力ないのは寂しいです'));
        $form->setElement($elem);
       
        return $form;
    }
    public function saveEntryAction()
    {
        //submitが押されていれば
        if($this->_getParam('submit'))
        {
          $form = $this->formCreation();
          //Validateを実行するためにformに値をセット
          //エラーが有った時各エレメントに値を戻す処理も兼ねてます
          $form->bind($this->_getAllParams());//bindメソッドは主に取得したパラメータを配列にしてセット
          
          
            $entry = new Bd_Orm_Main_Entry();//データベース入出力関係のクラスはこっちにある。
            $db = $entry->updateConnection();
                  
            $db->beginTransaction();
                  
            try
            {
              session_start();
              if($form->execValidate())
              {
                $entry
                  ->setBody($this->_getParam('body'))
                  ->setThreadId($this->_getParam('thread_id'))
                  ->setAccountId($this->_getParam('account_id'));
                $entry->save();
                $db->commit();
                $this->redirectAfterSave("thread/{$this->_getParam('thread_id')}/list");
              }
              else
              {
                  $db->rollback();
                  $_SESSION['form'] = $this->_getAllParams();
                  $this->redirectAfterSave("thread/{$this->_getParam('thread_id')}/list#entry-form");
              }
            
            }
            catch (Exception $e)
            {
              $db->rollBack();
              throw $e;
            }
          }
          else
          {
            $this->redirectAfterSave("thread/{$this->_getParam('thread_id')}/list#entry-form");
          }
         
    }
        
    
}
?>
