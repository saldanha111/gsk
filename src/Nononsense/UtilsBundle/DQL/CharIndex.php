<?php
namespace Nononsense\UtilsBundle\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * CharIndexFunction ::= "CHARINDEX" "(" ArithmeticPrimary "," ArithmeticPrimary ")"
 */
class CharIndex extends FunctionNode
{

    public $find = null;
    public $column = null;
    public $from = null;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->find = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->column = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->from = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'CHARINDEX(' .
            $this->find->dispatch($sqlWalker) . ', ' .
            $this->column->dispatch($sqlWalker) . ', ' .
            $this->from->dispatch($sqlWalker) .
        ')';
    }
}