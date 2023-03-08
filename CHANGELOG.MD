# SIGAT Changelog

## [1.4.0] - 2023-03-07

### Adicionado

- [Busca]\
Busca por número de lacre\
Número do chamado clicável na busca rápida

- [Painel]\
Coluna 'Região', referente ao local do chamado\
Impressão dos chamados pelo painel\
Botão para resetar os filtros do painel

- [Chamado]\
Botão para impressão do chamado\
Fechamento manual do chamado para casos excepcionais

### Modificado

- [Painel]\
Permite a visualização dos chamados sem restrição do responsável atual\
As colunas 'Prioridade' e 'Status' foram agrupadas

- [Interação]\
Os equipamentos agora são ordenados na tela de registro de interação

### Corrigido

- [Chamado]\
Alterações nos equipamentos do chamado não eram persistidas\
Atribuição incorreta de status para todos os chamados de um equipamento\
Horário dos artigos do Otobo com exibição incorreta (+3h)\
Impossibilidadede finalizar o chamado após remover um equipamento

- [Interação]\
Sinalização de entrega removida incorretamente

- [Arquivo]\
Erro de ordenação no painel do arquivo

- [Outros]\
Pequenos ajustes visuais

## [1.3.7] - 2022-10-20

### Corrigido

- [Painel]\
Ordenação incorreta dos chamados \
Clique com o botão direito dispara o evento de abrir a tela do chamado

### Adicionado

- [Painel]\
Clique com o botão de rolagem (scroll) no painel de chamados abre a tela do chamado em
outra aba\
Filtros do painel de chamados são salvos em cookies

- [Outros]\
Link para o changelog no rodapé

## [1.3.6] - 2022-10-11

### Adicionado

- [Painel]\
O botão de cada fila exibe o total de chamados

### Modificado

- [Painel]\
Data de abertura não exibe mais a hora, minuto e segundo da criação do chamado

## [1.3.5] - 2022-09-29

### Corrigido

- [Interação]\
Contagem incorreta de equipamentos pendentes

## [1.3.4] - 2022-09-28

### Modificado
- [Busca]\
Os resultados da busca rápida agora busca os dez últimos registros
- [Arquivo]\
O arquivo agora traz os últimos 500 registros
- [Interação]\
A tela de registro de interação foi ligeiramente diminuída para caber em telas menores

## [1.3.3] - 2022-09-28

### Corrigido
- [Interação]\
Timestamps de alteracões de equipamentos no chamado não eram registrados\
Finalização não esperada do chamado quando havia equipamentos em espera
- [Chamado]\
Exibição de quantidade incorreta de anexos buscados do OTRS

### Adicionado
- [Chamado]\
Novo botão para exibir o endereço do local do chamado\
Alteração de prioridade é registrada no log de eventos e no histórico do chamado

### Removido
- [Painel]\
Atualização automática dos painéis removida

## [1.2.3] - 2022-09-04

### Corrigido
- [Busca]\
Erro ao usar a busca rápida

### Adicionado
- [Painel]\
A última fila visitada agora é gravada em cookie para melhorar a navegação

## [1.2.2] - 2022-07-04

### Corrigido
- [Chamado]\
Retornado valor 0 para ID do chamado após a importação.
- [Interação]\
Registro duplicado de interações do tipo Entrega

## [1.2.1] - 2022-06-27

### Corrigido
- [Chamado]\
Remoção indevida de equipamentos

## [1.2.0] - 2022-06-24

### Corrigido
- [Chamado]\
Falha ao inserir novo equipamento\
Remoção indevida de equipamentos\
Rodapé sobrepondo a tabela de equipamentos

- [Interação]\
Texto do registro de falha de entrega não é persistido\
Renderização do laudo técnico com dados inconsistentes\
Criação de interação inválida após erros no envio de arquivos

- [Triagem]\
Usuário pode ter acesso à *view* de Triagem sem ter permissão

### Modificado
- [Chamado]\
Sinalização de equipamentos em aberto na lista de equipamentos

### Adicionado
- [Triagem]\
Comunicação com OTRS diretamente via API (devolução, importação e encerramento)\
Painel com as informações em tempo real obtidos do OTRS\
Descrição do ticket separado por artigo na tela de triagem\
Download dos anexos diretamente da base de dados do OTRS

- [Chamado] \
Descrição do ticket separado por artigo na tela de chamado\
Sinalização de prioridade no chamado\
Download dos anexos diretamente da base de dados do OTRS

- [Painel]\
Coluna de percentual de conclusão no painel de chamados\
Indicação visual do tempo decorrido desde a última interação no painel de chamados

- [Busca]\
Pesquisa pelo número do chamado na busca rápida

### Removido
- [Triagem]\
Removido armazenamento de tickets para triagem\
Removido armazenamento do conteúdo dos anexos

